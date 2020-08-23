<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Schedule;
use App\User;
use Auth;
use DB;
use Carbon\Carbon;

class ScheduleController extends Controller
{

    public function index()
    {
        $schedules = Schedule::withCount('shifts')->get();

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
            'availableday_mon' => 1,
            'availableday_tues' => 1,
            'availableday_wed' => 1,
            'availableday_thur' => 1,
            'availableday_fri' => 1,
            'availableday_sat' => 1,
            'availableday_sun' => 1
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
        $schedule->availableday_mon = $request->availableday_mon;
        $schedule->availableday_tues = $request->availableday_tues;
        $schedule->availableday_wed = $request->availableday_wed;
        $schedule->availableday_thur = $request->availableday_thur;
        $schedule->availableday_fri = $request->availableday_fri;
        $schedule->availableday_sat = $request->availableday_sat;
        $schedule->availableday_sun = $request->availableday_sun;
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
}
