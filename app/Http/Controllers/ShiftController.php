<?php

namespace App\Http\Controllers;

use App\Shift;
use App\Schedule;
use App\User;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    /*
    API Usage
    index   ->  GET	    /schedules/{schedule_id}/shifts
    store   ->  POST    /schedules/{schedule_id}/shifts ('location_id', 'time_start', 'time_end', 'min_participants', 'max_participants')
    show    ->  GET	    /schedules/{schedule_id}/shifts/{shift_id}
    update  ->  PATCH	  /schedules/{schedule_id}/shifts/{shift_id} ('location_id', 'time_start', 'time_end', 'min_participants', 'max_participants')
    destroy ->  DELETE	/schedules/{schedule_id}/shifts/{shift_id}

    addUserToShift        ->  POST  ->  /schedules/joinshift ('user_id', 'shift_id')
    removeUserFromShift   ->  POST  ->  /schedules/leaveshift ('user_id', 'shift_id')
    */


    public function index($scheduleid)
    {
        $shifts = Schedule::find($scheduleid)->shifts;

        return response()->json($shifts);
    }



    public function store($scheduleid, Request $request)
    {
        $shift = Shift::create([
            'schedule_id' => $scheduleid,
            'location_id' => $request->location_id,
            'time_start' => $request->time_start,
            'time_end' => $request->time_end,
            'min_participants' => $request->min_participants,
            'max_participants' => $request->max_participants
        ]);

        $data = [
            'data' => $shift,
            'status' => (bool) $shift,
            'message' => $shift ? 'Shift Created!' : 'Error Creating Shift',
        ];

        return response()->json($data);
    }



    public function show($scheduleid, $shiftid)
    {
        $shift = Shift::find($shiftid);
        return response()->json($shift);
    }



    public function update($scheduleid, Request $request, $shiftid)
    {
      $shift = Shift::find($shiftid);
      $shift->location_id = $request->location_id;
      $shift->time_start = $request->time_start;
      $shift->time_end = $request->time_end;
      $shift->min_participants = $request->min_participants;
      $shift->max_participants = $request->max_participants;
      $shift->save();

      return response()->json($shift);
    }



    public function destroy($scheduleid, $shiftid)
    {
      Shift::destroy($shiftid);
      $data = [
          'message' => 'Location Deleted!',
      ];
      return response()->json($data);
    }




    // Custom Functions

    public function addUserToShift(Request $request)
    {
        $userid = $request->user_id;
        $shiftid = $request->shift_id;

        $user = User::find($userid);
        $user->shifts()->detach($shiftid); // First detach if already exists
        $user->shifts()->attach($shiftid);

        $data = [
            'message' => 'Successfully added to shift!',
            'shifts' => $user->shifts
        ];
        return response()->json($data);
    }


    public function removeUserFromShift(Request $request)
    {
        $userid = $request->user_id;
        $shiftid = $request->shift_id;

        $user = User::find($userid);
        $user->shifts()->detach($shiftid); // First detach if already exists

        $data = [
            'message' => 'Successfully removed from shift!',
            'teams' => $user->shifts
        ];
        return response()->json($data);
    }
}
