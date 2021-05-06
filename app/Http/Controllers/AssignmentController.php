<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Schedule;
use App\Models\Shift;
use App\Models\User;
use App\Models\Team;
use Auth;
use DB;
use Carbon\Carbon;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;

class AssignmentController extends Controller
{
    public function shiftAutoAssign(Request $request)
    {
      $user = Auth::user();
      $team = $user->teams->find($request->team_id);
      $reset = $request->reset; // Should we remove current assignments and start over? Boolean
      $minOrMax = $request->min_or_max; // 'MIN' or 'MAX', whether to fill all slots or just minimum participants

      if ($team) {
        if ($user->hasRole('team_admin', $team)) {     
          $schedule = $team->schedules->find($request->schedule_id);
          if (!$schedule) return RB::error(404); // Schedule not found

          // If user is a team admin, start the auto-assign process, return schedule
          $results = $this->shiftLoop($team, $schedule, $reset, $minOrMax);

          $data = [
            'schedule' => Schedule::with('shifts')->find($schedule->id)
            //'count' => $results
          ];

          return RB::success($data);

        } else {
          return RB::error(403); // access denied
        }

      } else {
        return RB::error(404); // team not found
      }


    }


    public function shiftLoop($team, $schedule, $reset, $minOrMax)
    {
      $start_date = new \Carbon\CarbonImmutable($schedule->date_start);
      $end_date = $start_date->add(8, 'days');
      $shift_status = $team->setting_shift_assignment_autoaccept ? 2 : 0;



      // Get all mandatory shifts with open slots
      $shifts = collect($schedule->shifts()->where('mandatory',1)->get());
      if ($reset) {
        foreach ($shifts as $shift) {
          $shift->users()->detach(); // Remove all current assignments from shift, if requested
        }
      }


      $shifts = collect($schedule->shifts()->where('mandatory',1)->get());
      $shifts = $shifts->shuffle(); // Randomize shift order. In this way, we reduce the chance of a user be assigned the same shift slot every week.
      $shifts = $shifts->sortBy('day_of_week'); // Fill week day shifts first


      foreach ($shifts as $shift) {

        if ($minOrMax == 'MIN') {
          $max_slots = $shift->min_participants;
        } else {
          $max_slots = $shift->max_participants;
        }

        $available_users = collect($this->getAvailableUsers($team, $shift, $schedule));
        $available_users = $this->sortUsers($available_users);


        if ($available_users->count() > 0) {

          $i = $shift->users()->count();

          foreach ($available_users as $u) {
            if ($i >= $max_slots) break;

            $u->shifts()->attach($shift);
            $u->shifts()->updateExistingPivot($shift->id, ['status' => $shift_status]);

            $i++;
          }

        }

      } // END OF SHIFT PASS



      return true;

    }


    public function sortUsers($members) {

      $data = $members->sortBy([
        ['sort_rank', 'desc'], // Priority giving to FTS and lower availability
        ['shifts_current', 'asc'], // Give WEEKLY assignments to as many users as possible
        ['shifts_30days', 'asc'], // Make sure MONTHLY schedule is balanced
      ]);

      $data->values()->all();

      return $data;
    }


    public function getTeamUsers($team, $schedule) {
      $start_date = new \Carbon\CarbonImmutable($schedule->date_start);

      $members = collect($team->users()
                      ->with('available_hours', 'shifts')
                      ->whereHas('available_hours')
                      ->withCount([
                        'shifts as shifts_30days' => function (Builder $query) use ($start_date) {
                          $query->where('time_start', '>=', $start_date->sub(1, 'month'))
                                ->where('time_start', '<', $start_date)
                                ->where('shift_user.status', '<>', 3);
                        },
                        'shifts as shifts_14days' => function (Builder $query) use ($start_date) {
                          $query->where('time_start', '>=', $start_date->sub(14, 'days'))
                                ->where('time_start', '<', $start_date)
                                ->where('shift_user.status', '<>', 3);
                        },
                        'shifts as shifts_7days' => function (Builder $query) use ($start_date) {
                          $query->where('time_start', '>=', $start_date->sub(7, 'days'))
                                ->where('time_start', '<', $start_date)
                                ->where('shift_user.status', '<>', 3);
                        },
                        'shifts as shifts_current' => function (Builder $query) use ($schedule) {
                          $query->where('schedule_id', $schedule->id);
                        } 
                      ])
                      ->get());
      
      return $members->shuffle();
    }


    public function getAvailableUsers($team, $shift, $schedule) {
      $windows = array();
      $available_users = array();
      $start_date = new Carbon($shift->time_start);
      $end_date = new Carbon($shift->time_end);
      $dow = $start_date->dayOfWeekIso;
      $shift_start_hour = $start_date->hour;
      $shift_end_hour = $end_date->hour;
      if ($end_date->minute > 0) $shift_end_hour += 1; // Round up the end hour to the next full hour
      $shift_length = $shift_end_hour - $shift_start_hour; // Get the shift duration in hours (rounded up)


      $members = $this->getTeamUsers($team, $schedule);


      // Create array of all availability windows encompassing this shift
      for ($i = 0; $i < $shift_length; $i++) {
        $window_start_1 = Carbon::create(2000, 1, 1, ($shift_start_hour + $i), 0, 0)->toTimeString();
        $window_start_2 = Carbon::create(2000, 1, 1, ($shift_start_hour + $i + 1), 0, 0)->toTimeString();

        array_push($windows, array($i, $window_start_1, $window_start_2));
      }



      foreach ($members as $member) {
        $is_available = true;


        // Check if the user already is assigned THIS DAY
        if ($is_available) {
          $check = $member->shifts()->where('time_start', '>', $start_date->copy()->startOfDay())->where('time_start','<', $start_date->copy()->endOfDay())->get();
          if($check->count() > 0) $is_available = false;
        }


        // Loop through all WINDOWS and check against user availability
        // If window is not found, remove user from list and exit loop. Not available for this shift.
        if ($is_available) {
          foreach ($windows as $window) {
            $check = $member->user_availabilities()->where('day_of_week', $dow)->where('start_time', $window[1])->where('available', 1)->get();
            
            if($check->count() == 0) {
              $is_available = false;
              break;
            }
          }
        }



        // Next, check for CONFLICTS with user's existing shift assignments
        // Shifts at same time; Adjacent shifts at different location
        if ($is_available) {
          $check = $member->shifts()->whereBetween('time_start', [$start_date, $end_date])->get();
          if($check->count() > 0) $is_available = false;
        }

        if ($is_available) {
          $check = $member->shifts()->whereBetween('time_end', [$start_date, $end_date])->get();
          if($check->count() > 0) $is_available = false;
        }

        if ($is_available) {
          $check = $member->shifts()->where('location_id', '!=', $shift->location_id)->where('time_start', '=', $end_date)->get();
          if($check->count() > 0) $is_available = false;
        }

        if ($is_available) {
          $check = $member->shifts()->where('location_id', '!=', $shift->location_id)->where('time_end', '=', $start_date)->get();
          if($check->count() > 0) $is_available = false;
        }
        


        // Assign a SORT RANK based on FTS status and availablility
        // Rank is determined by number of hours NOT AVAILABLE multiplied by FTS status
        // Lower availability is assigned first
        if ($is_available) {
          $fts = $member->fts_status;
          $curr = $member->shifts_current;
          $not_avail = 168 - ($member->available_hours_count);

          if ($fts == 1 && $curr <= 2) { // Regular pioneer without shifts this week
            $sort_rank = 2 * $not_avail;
          } else if ($fts == 2 && $curr <= 3) { // SP/Missionary without shifts this week
            $sort_rank = 3 * $not_avail;
          } else if ($fts > 2 && $curr == 0) { // Other SFTS without shifts this week
            $sort_rank = 1 * $not_avail;
          } else {
            $sort_rank = $not_avail;
          }

          $members['sort_rank'] = $sort_rank;
          
        }


        if ($is_available) array_push($available_users, $member);
      }


      return $available_users;

    }





    /**
     * 
     * make trade
     *  - ROLE: team member
     * 
     */
    public function switchAssignments(Request $request)
    {
      $user = Auth::user();
      $team = $user->teams->find($request->team_id);
      $schedule = $user->schedules->find($request->schedule_id);

      
      if (!$schedule || !$team) return RB::error(404); // schedule or team not found


      if ($user->hasRole(['team_admin'], $team) || $user->hasRole('super_admin', null)) {
        $user1 = User::find($request->user1_id);
        $user2 = User::find($request->user2_id);
        $shift1 = $request->shift1_id;
        $shift2 = $request->shift2_id;
        $status1 = $request->status1;
        $status2 = $request->status2;


        $user1->shifts()->detach($shift1);
        $user1->shifts()->attach($shift2);
        $user1->shifts()->updateExistingPivot($shift2, ['status' => $status1]);

        $user2->shifts()->detach($shift2);
        $user2->shifts()->attach($shift1);
        $user2->shifts()->updateExistingPivot($shift1, ['status' => $status2]);

        $data = [
          'schedule' => Schedule::with('shifts')->find($request->schedule_id),
        ];

        return RB::success($data);

      } else {
        return RB::error(403); // access denied
      }
    }






    public function getAvailabilityWeight($team, $schedule) {
      $start_date = new \Carbon\CarbonImmutable($schedule->date_start);
      $end_date = $start_date->add(8, 'days');

      $data =  collect($team->users()
                            ->with('available_hours', 'shifts')
                            ->whereHas('available_hours')
                            ->withCount('available_hours')
                            ->withCount([
                              'shifts as shifts_30days' => function (Builder $query) use ($start_date) {
                                $query->where('time_start', '>=', $start_date->sub(1, 'month'))
                                      ->where('time_start', '<', $start_date)
                                      ->where('shift_user.status', '<>', 3);
                              },
                              'shifts as shifts_14days' => function (Builder $query) use ($start_date) {
                                $query->where('time_start', '>=', $start_date->sub(14, 'days'))
                                      ->where('time_start', '<', $start_date)
                                      ->where('shift_user.status', '<>', 3);
                              },
                              'shifts as shifts_7days' => function (Builder $query) use ($start_date) {
                                $query->where('time_start', '>=', $start_date->sub(7, 'days'))
                                      ->where('time_start', '<', $start_date)
                                      ->where('shift_user.status', '<>', 3);
                              },
                              'shifts as shifts_current' => function (Builder $query) use ($schedule) {
                                $query->where('schedule_id', $schedule->id);
                              } 
                            ])
                            ->get());
      
      $data = $this->sortUsers($data);

      return $data;
    }




    public function apiTest(Request $request) {
      $user = Auth::user();
      $team = $user->teams->find($request->team_id);
      $schedule = $team->schedules->find($request->schedule_id);

      $return = $this->getAvailabilityWeight($team, $schedule);

      $data = [
        'users' => $return
      ];

      return RB::success($data);
    }
}
