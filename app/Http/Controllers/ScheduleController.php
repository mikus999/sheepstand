<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\User;
use Auth;
use DB;
use Carbon\Carbon;

class ScheduleController extends Controller
{

    public function index($teamid)
    {
        $schedules = Schedule::withCount('shifts')
                        ->where('team_id','=',$teamid)
                        ->orderBy('date_start', 'asc')
                        ->get();

        return response()->json($schedules);
    }



    public function store(Request $request)
    {
        $user = Auth::user();
        $userid = $user->id;

        $schedule = Schedule::create([
            'team_id' => $request->team_id,
            'user_id' => $userid,
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
        $schedule = Schedule::find($id);
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

}
