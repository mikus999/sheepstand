<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\Shift;
use App\Models\User;
use App\Models\Team;
use Auth;
use DB;
use Carbon\Carbon;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;

class ScheduleController extends Controller
{
    public $template_date = '2001-01-01';

    public function index($teamid)
    {
      $user = Auth::user();
      $team = $user->teams->find($teamid);

      if ($team) {
        $schedules = Schedule::where('team_id','=',$teamid)
                        ->where('status','!=',9)
                        ->orderBy('date_start', 'asc')
                        ->get();

        return RB::success(['schedules' => $schedules]);
      } else {
        return RB::error(404); // team not found
      }
    }



    public function store(Request $request)
    {
        $user = Auth::user();
        $team = $user->teams->find($request->team_id);

        if ($team) {
          if ($user->hasRole('team_admin', $team)) {     
            $schedule = Schedule::create([
                'team_id' => $request->team_id,
                'user_id' => $user->id,
                'status' => 0,
                'date_start' => date($request->date_start), // YYYY-MM-DD
            ]);

            return RB::success(['schedule' => $schedule]);

          } else {
            return RB::error(403); // access denied
          }

        } else {
          return RB::error(404); // team not found
        }

    }



    public function show($id)
    {
      $user = Auth::user();
      $schedule = Schedule::with('shifts')->find($id);

      if ($schedule && $user->teams->find($schedule->team_id)) {
        return RB::success(['schedule' => $schedule]);
      } else {
        return RB::error(404); // schedule not found
      }

    }



    public function update(Request $request, $id)
    {
      $user = Auth::user();
      $schedule = Schedule::with('shifts')->find($id);

      if ($schedule) {
        $team = $user->teams->find($schedule->team_id);

        if ($team && $user->hasRole('team_admin', $team)) {     
          $schedule = Schedule::with('shifts')->find($id);
          $schedule->status = $request->status;
          $schedule->date_start = date($request->date_start); // YYYY-MM-DD
          $schedule->save();
          
          return RB::success(['schedule' => $schedule]);

        } else {
          return RB::error(403); // access denied
        }
      } else {
        return RB::error(404); // schedule not found
      }
    }



    public function destroy($id)
    {
      $user = Auth::user();
      $schedule = $user->schedules->find($id);

      if ($schedule) {
        $team = $user->teams->find($schedule->team_id);

        if ($team && $user->hasRole('team_admin', $team)) {     
          Schedule::destroy($id);          
          return RB::success();
        } else {
          return RB::error(403); // access denied
        }
      } else {
        return RB::error(404); // schedule not found
      }
    }



    public function getShiftCounts($id, $date, $dayOfWeek)
    {
        $date = (new Carbon($date))->addDays($dayOfWeek)->toDateString();


        $shiftcount = DB::table('shifts')
            ->select(DB::raw('COUNT(*) as shift_count'))
            ->where('schedule_id', $id)
            ->whereDate('time_start', $date)
            ->get();

        return response()->json($shiftcount);
    }


    public function updateStatus(Request $request, $id)
    {
      $user = Auth::user();
      $schedule = Schedule::with('shifts')->find($id);
      $team = $schedule->team;

      if ($schedule && $user->teams->find($schedule->team_id)) {
        if ($user->hasRole('team_admin', $team)) {     
          $schedule->status = $request->status;
          $schedule->save();

          return RB::success(['schedule' => $schedule]);
          
        } else {
          return RB::error(403); // access denied
        }
      } else {
        return RB::error(404); // schedule not found
      }
    }


    public function getTemplates($teamid)
    {
      $user = Auth::user();
      $team = $user->teams->find($teamid);

      if (!$team) return RB::error(404); // team not found

      if ($user->hasRole('team_admin', $team)) {     
        $schedules = Schedule::where('team_id','=',$teamid)
                      ->where('status','=',9)
                      ->orderBy('date_start', 'asc')
                      ->get();

        return RB::success(['schedules' => $schedules]);

      } else {
        return RB::error(403); // access denied
      }
    }


    public function newTemplate(Request $request)
    {
        $user = Auth::user();
        $team = Team::find($request->team_id);

        if (!$team) return RB::error(404); // team not found

        if ($user->hasRole('team_admin', $team)) {
          
          $schedule = Schedule::create([
            'team_id' => $team->id,
            'user_id' => $user->id,
            'status' => 9,
            'date_start' => $this->template_date, // YYYY-MM-DD
            'template_name' => $request->template_name,
          ]);

          return RB::success(['schedule' => $schedule]);

        } else {
          return RB::error(403); // access denied
        }

    }



    public function makeFromTemplate(Request $request, $id)
    {
        $user = Auth::user();
        $template = Schedule::find($id);
        $team = Team::find($template->team_id);
        $template_date = Carbon::createFromDate($template->date_start);
        $schedule_date = Carbon::createFromDate($request->date_start);
        $diff = $template_date->diffInDays($schedule_date); // Difference in days between template Monday (2001-01-01) and new schedule Monday

        if (!$template || !$team) return RB::error(404); // template or team not found

        if ($user->hasRole('team_admin', $team)) {

          $schedule = Schedule::create([
            'team_id' => $template->team_id,
            'user_id' => $user->id,
            'status' => 0,
            'date_start' => date($request->date_start), // YYYY-MM-DD
          ]);

          
          foreach ($template->shifts as $shift) {
            $shift_start = Carbon::createFromDate($shift->time_start)->addDays($diff);
            $shift_end = Carbon::createFromDate($shift->time_end)->addDays($diff);

            $newshift = Shift::create([
              'schedule_id' => $schedule->id,
              'location_id'=> $shift->location_id,
              'time_start' => $shift_start,
              'time_end' => $shift_end,
              'min_participants' => $shift->min_participants,
              'max_participants' => $shift->max_participants,
              'mandatory' => $shift->mandatory
            ]);
          }

          return RB::success(['schedule' => $schedule]);

        } else {
          return RB::error(403); // access denied
        }
        
    }



    public function saveAsTemplate(Request $request, $id)
    {
        $user = Auth::user();
        $schedule = Schedule::find($id);
        $team = Team::find($schedule->team_id);
        $schedule_date = Carbon::createFromDate($schedule->date_start);
        $template_date = Carbon::createFromDate($this->template_date);
        $diff = $template_date->diffInDays($schedule_date); // Difference in days between template Monday (2001-01-01) and new schedule Monday


        if (!$schedule || !$team) return RB::error(404); // schedule or team not found


        if ($user->hasRole('team_admin', $team)) {

          $template = Schedule::create([
            'team_id' => $team->id,
            'user_id' => $user->id,
            'status' => 9,
            'date_start' => $this->template_date,
            'template_name' => $request->template_name,
          ]);

          
          foreach ($schedule->shifts as $shift) {
            $shift_start = Carbon::createFromDate($shift->time_start)->subDays($diff);
            $shift_end = Carbon::createFromDate($shift->time_end)->subDays($diff);

            $newshift = Shift::create([
              'schedule_id' => $template->id,
              'location_id'=> $shift->location_id,
              'time_start' => $shift_start,
              'time_end' => $shift_end,
              'min_participants' => $shift->min_participants,
              'max_participants' => $shift->max_participants,
              'mandatory' => $shift->mandatory
            ]);
          }

          return RB::success(['schedule' => $schedule]);

        } else {
          return RB::error(403); // access denied
        }
        
    }


}
