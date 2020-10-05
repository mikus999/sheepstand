<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\Request;
use Auth;
use Helper;

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
    changeUserShiftStatus ->  POST  ->  /schedules/shiftuserstatus ('user_id', 'shift_id', 'status')
    */



    /**
     * 
     * Show all shifts for the given schedule
     *  - ROLE: team member
     * 
     *  GET -> /schedules/{schedule_id}/shifts
     * 
     */
    public function index($scheduleid)
    {
        $user = Auth::user();
        $shifts = [];

        $schedule = $user->schedules->find($scheduleid);

        if ($schedule) {
            $shifts = $schedule->shifts()->with('location', 'users')
                        ->orderBy('shifts.location_id')
                        ->orderBy('shifts.time_start')
                        ->get();
        }
        
        return response()->json($shifts);
    }



    /**
     * 
     * Create a new shift
     *  - ROLE: team_admin
     * 
     */
    public function store($scheduleid, Request $request)
    {
        $data = ['message' => 'Access Denied'];
        $user = Auth::user();
        $schedule = $user->schedules->find($scheduleid);

        if ($schedule) {
            $team = $user->teams->find($schedule->team_id);

            if ($user->hasRole('team_admin', $team)) {
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
            }
        }

        return response()->json($data);
    }



    /**
     * 
     * Display details for the given shift
     *  - ROLE: team member
     * 
     */
    public function show($scheduleid, $shiftid)
    {
        $user = Auth::user();
        $schedule = $user->schedules->find($scheduleid);
        $shift = null;

        if ($schedule) {
            $shift = Shift::find($shiftid);
        }

        return response()->json($shift);
    }



    /**
     * 
     * Update shift details
     *  - ROLE: team_admin
     * 
     */
    public function update($scheduleid, Request $request, $shiftid)
    {
        $user = Auth::user();
        $schedule = $user->schedules->find($scheduleid);
        $shift = null;

        if ($schedule) {
            $team = $user->teams->find($schedule->team_id);

            if ($user->hasRole('team_admin', $team)) {
                $shift = Shift::find($shiftid);
                $shift->location_id = $request->location_id;
                $shift->time_start = $request->time_start;
                $shift->time_end = $request->time_end;
                $shift->min_participants = $request->min_participants;
                $shift->max_participants = $request->max_participants;
                $shift->save();
            }
        }

        return response()->json($shift);
    }


    /**
     * 
     * Destroy/delete shift,
     *  - ROLE: team_admin
     * 
     */
    public function destroy($scheduleid, $shiftid)
    {
        $data = ['message' => 'Access Denied'];
        $user = Auth::user();
        $schedule = $user->schedules->find($scheduleid);
        $shift = null;

        if ($schedule) {
            $team = $user->teams->find($schedule->team_id);

            if ($user->hasRole('team_admin', $team)) {        
                Shift::destroy($shiftid);
                $data = [
                    'message' => 'Location Deleted!',
                ];
            }
        }

        return response()->json($data);
    }




    /**
     * 
     * Add a user to a shift
     *  - ROLE: team_admin, or user adding self
     * 
     */
    public function addUserToShift(Request $request)
    {
        $data = ['message' => 'Access Denied'];
        $user = Auth::user();
        $userid = $request->user_id;
        $shiftid = $request->shift_id;
        $status = $request->status;

        $shift = Shift::find($shiftid);
        $schedule = $user->schedules->find($shift->schedule_id);

        if ($schedule) {
            $team = $user->teams->find($schedule->team_id);

            if ($user->hasRole('team_admin', $team) || $userid === $user->id) {                 
                if (is_null($status)) {
                    $status = 0;
                    $request->status = $status;
                }

                $targetUser = User::find($userid);
                $targetUser->shifts()->detach($shiftid); // First detach if already exists
                $targetUser->shifts()->attach($shiftid);

                $this->changeUserShiftStatus($request);

                $shiftusers = Shift::find($shiftid)->users()->get();

                $data = [
                    'shiftusers' => $shiftusers,
                    'usershifts' => $targetUser->shifts
                ];
            }
        }

        return response()->json($data);
    }



    /**
     * 
     * Remove a user from a shift
     *  - ROLE: team_admin, or user removing self
     * 
     */
    public function removeUserFromShift(Request $request)
    {
        $data = ['message' => 'Access Denied'];
        $user = Auth::user();
        $userid = $request->user_id;
        $shiftid = $request->shift_id;

        $shift = Shift::find($shiftid);
        $schedule = $user->schedules->find($shift->schedule_id);

        if ($schedule) {
            $team = $user->teams->find($schedule->team_id);

            if ($user->hasRole('team_admin', $team) || $userid === $user->id) {     
                $targetUser = User::find($userid);
                $targetUser->shifts()->detach($shiftid); // First detach if already exists

                $shiftusers = Shift::find($shiftid)->users()->get();

                $data = [
                    'shiftusers' => $shiftusers,
                    'usershifts' => $targetUser->shifts
                ];
            }
        }

        return response()->json($data);
    }



    /**
     * 
     * Change a user's shift status
     *  - ROLE: team member
     * 
     */
    public function changeUserShiftStatus(Request $request)
    {
        $data = ['message' => 'Access Denied'];
        $user = Auth::user();
        $userid = $request->user_id;
        $shiftid = $request->shift_id;
        $status = $request->status;

        $shift = Shift::find($shiftid);
        $schedule = $user->schedules->find($shift->schedule_id);

        if ($schedule) {
            $targetUser = User::find($userid);
            $targetUser->shifts()->updateExistingPivot($shiftid, ['status' => $status]);

            $shiftusers = Shift::find($shiftid)->users()->get();
            $data = [
                'shiftusers' => $shiftusers
            ];
        }


        return response()->json($data);

    }



    /**
     * 
     * Return all users assigned to a shift
     *  - ROLE: team member
     * 
     */
    public function getShiftUsers($id)
    {
        $user = Auth::user();
        $shiftusers = null;
        $shift = Shift::find($id);

        if ($shift) {
            $schedule = $user->schedules->find($shift->schedule_id);

            if ($schedule) {
                $shiftusers = $shift->users()->get();
            }
        }

        return response()->json($shiftusers);
    }
}
