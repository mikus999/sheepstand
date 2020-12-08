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

class ScheduleController extends Controller
{
    public $template_date = '2001-01-01';

    public function index($teamid)
    {
        $schedules = Schedule::withCount('shifts')
                        ->where('team_id','=',$teamid)
                        ->where('status','!=',9)
                        ->orderBy('date_start', 'asc')
                        ->get();

        return response()->json($schedules);
    }



    public function store(Request $request)
    {
        $user = Auth::user();

        $schedule = Schedule::create([
            'team_id' => $request->team_id,
            'user_id' => $user->id,
            'status' => 0,
            'date_start' => date($request->date_start), // YYYY-MM-DD
        ]);


        $data = [
            'schedule' => $schedule,
            'status' => (bool) $schedule,
            'message' => $schedule ? 'Schedule Created!' : 'Error Creating Schedule',
        ];

        return response()->json($data);
    }



    public function show($id)
    {
        $schedule = Schedule::with('shifts')->find($id);
        return response()->json($schedule);
    }



    public function update(Request $request, $id)
    {
        $schedule = Schedule::find($id);
        $schedule->status = $request->status;
        $schedule->date_start = date($request->date_start); // YYYY-MM-DD
        $schedule->save();

        return response()->json($schedule);
    }



    public function destroy($id)
    {
      Schedule::destroy($id);
      $data = [
          'message' => 'Location Deleted!',
      ];
      return response()->json($data);
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
        $schedule = Schedule::find($id);
        $schedule->status = $request->status;
        $schedule->save();

        return response()->json($schedule);
    }


    public function getTemplates($teamid)
    {
        $schedules = Schedule::withCount('shifts')
                        ->where('team_id','=',$teamid)
                        ->where('status','=',9)
                        ->orderBy('date_start', 'asc')
                        ->get();

        return response()->json($schedules);
    }


    public function newTemplate(Request $request)
    {
        $data = [];
        $user = Auth::user();
        $teamid = $request->team_id;
        $team = Team::find($teamid);

        if (($user->hasRole('team_admin', $team) || $user->hasRole('super_admin', null))) {
          
          $schedule = Schedule::create([
            'team_id' => $teamid,
            'user_id' => $user->id,
            'status' => 9,
            'date_start' => $this->template_date, // YYYY-MM-DD
            'template_name' => $request->template_name,
          ]);


          $data = [
            'schedule' => $schedule,
          ];
        }

        return response()->json($data);
    }



    public function makeFromTemplate(Request $request, $id)
    {
        $user = Auth::user();
        $template = Schedule::find($id);
        $team = Team::find($template->team_id);
        $template_date = Carbon::createFromDate($template->date_start);
        $schedule_date = Carbon::createFromDate($request->date_start);
        $diff = $template_date->diffInDays($schedule_date); // Difference in days between template Monday (2001-01-01) and new schedule Monday


        if (($user->hasRole('team_admin', $team) || $user->hasRole('super_admin', null))) {

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
              'max_participants' => $shift->max_participants
            ]);
          }
        }
        
        return response()->json($schedule);
    }



    public function saveAsTemplate(Request $request, $id)
    {
        $user = Auth::user();
        $schedule = Schedule::find($id);
        $team = Team::find($schedule->team_id);
        $schedule_date = Carbon::createFromDate($schedule->date_start);
        $template_date = Carbon::createFromDate($this->template_date);
        $diff = $template_date->diffInDays($schedule_date); // Difference in days between template Monday (2001-01-01) and new schedule Monday


        if (($user->hasRole('team_admin', $team) || $user->hasRole('super_admin', null))) {

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
              'max_participants' => $shift->max_participants
            ]);
          }
        }
        
        return response()->json($schedule);
    }
}
