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
      $minOrMax = $request->minormax; // 'MIN' or 'MAX', whether to fill all slots or just minimum participants

      if ($team) {
        if ($user->hasRole('team_admin', $team)) {     
          $schedule = $team->schedules->find($request->schedule_id);
          if (!$schedule) return RB::error(404); // Schedule not found

          // If user is a team admin, start the auto-assign process, return schedule
          $results = $this->shiftLoop($user, $team, $schedule, $reset, $minOrMax);

          $data = [
            'schedule' => Schedule::with('shifts')->find($schedule->id)
          ];

          return RB::success($data);

        } else {
          return RB::error(403); // access denied
        }

      } else {
        return RB::error(404); // team not found
      }


    }


    public function shiftLoop($user, $team, $schedule, $reset, $minOrMax)
    {
      // Get all mandatory shifts
      $shifts = collect($schedule->shifts()->where('mandatory',1)->get());
      $shifts = $shifts->shuffle(); // Randomize shift order. In this way, we reduce the chance of a user be assigned the same shift slot every week.

      $members = $team->users()
                      ->with('user_availabilities')
                      ->whereHas('user_availabilities')
                      ->get();


      foreach ($shifts as $shift) {
        if ($reset) $shift->users()->detach(); // Remove all current assignments from shift, if requested

        if ($minOrMax == 'MIN') {
          $max_slots = $shift->min_participants;
        } else {
          $max_slots = $shift->max_participants;
        }

        $shift_status = $team->setting_shift_assignment_autoaccept ? 2 : 0;

        $available_users = collect($this->getAvailableUsers($team, $shift, $members));
        $available_users = $available_users->shuffle();

        if ($available_users->count() > 0) {
          // TODO Factors: fts status, number of assignments, weekly availability weight, has car, marriage mate


          foreach ($available_users as $u) {
            $u->shifts()->attach($shift);
            $u->shifts()->updateExistingPivot($shift->id, ['status' => $shift_status]);

            if ($max_slots - $shift->users()->count() == 0) break;
          }
        }

      }

      return $schedule;

    }


    public function getAvailableUsers($team, $shift, $members) {
      $windows = array();
      $available_users = array();
      $start_date = new Carbon($shift->time_start);
      $end_date = new Carbon($shift->time_end);
      $dow = $start_date->dayOfWeekIso;
      $shift_start_hour = $start_date->hour;
      $shift_end_hour = $end_date->hour;
      if ($end_date->minute > 0) $shift_end_hour += 1; // Round up the end hour to the next full hour
      $shift_length = $shift_end_hour - $shift_start_hour; // Get the shift duration in hours (rounded up)


      // Create array of all availability windows encompassing this shift
      for ($i = 0; $i < $shift_length; $i++) {
        $window_start_1 = Carbon::create(2000, 1, 1, ($shift_start_hour + $i), 0, 0)->toTimeString();
        $window_start_2 = Carbon::create(2000, 1, 1, ($shift_start_hour + $i + 1), 0, 0)->toTimeString();

        array_push($windows, array($i, $window_start_1, $window_start_2));
      }




      // Loop through users who have weekly availability this day of week
      foreach ($members as $member) {
        $is_available = true;

        // Loop through all windows and check against user availability
        // If window is not found, remove user from list and exit loop. Not available for this shift.
        foreach ($windows as $window) {
          $check = $member->user_availabilities()->where('day_of_week', $dow)->where('start_time', $window[1])->where('available', 1)->get();
          
          if($check->count() == 0) {
            $is_available = false;
            break;
          }

        }


        // Next, check for conflicts with user's existing shift assignments
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
          $check = $member->shifts()->where('location_id', '!=', $shift->location_id)->where('time_start', '=', $end_date);
          if($check->count() > 0) $is_available = false;
        }

        if ($is_available) {
          $check = $member->shifts()->where('location_id', '!=', $shift->location_id)->where('time_end', '=', $start_date);
          if($check->count() > 0) $is_available = false;
        }


        if ($is_available) array_push($available_users, $member);
      }












      return $available_users;

    }


}
