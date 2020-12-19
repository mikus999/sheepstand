<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\Schedule;
use App\Models\User;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
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
            $shifts = $schedule->shifts()->with('schedule', 'location', 'users')
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
                    'max_participants' => $request->max_participants,
                    'mandatory' => $request->mandatory
                ]);

                $data = [
                    'shift' => $shift,
                    'schedule' => Schedule::with('shifts')->find($scheduleid),
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
                $shift->mandatory = $request->mandatory;
                $shift->save();
            }

            $data = [
                'shift' => $shift,
                'schedule' => Schedule::with('shifts')->find($scheduleid),
            ];
        }


        return response()->json($data);
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

        if ($schedule) {
            $team = $user->teams->find($schedule->team_id);

            if ($user->hasRole('team_admin', $team)) {        
                Shift::destroy($shiftid);
            }

            $data = [
                'schedule' => Schedule::with('shifts')->find($scheduleid),
            ];
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
        $shift = Shift::find($shiftid);
        $schedule = $user->schedules->find($shift->schedule_id);
        $status = $request->status;

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

                $shiftusers = $this->shiftUsers($shift);
                $usershifts = $this->userShifts($targetUser);


                $data = [
                    'shiftusers' => $shiftusers,
                    'usershifts' => $usershifts,
                    'trades' => $this->teamTrades($team),
                    'teamusers' =>  $team->users()->with('shifts')->get()
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

                $shiftusers = $this->shiftUsers($shift);
                $usershifts = $this->userShifts($targetUser);


                $data = [
                    'shiftusers' => $shiftusers,
                    'usershifts' => $usershifts,
                    'trades' => $this->teamTrades($team),
                    'teamusers' =>  $team->users()->with('shifts')->get()
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
        $targetUser = User::find($request->user_id);
        $shift = Shift::find($request->shift_id);
        $schedule = Schedule::find($shift->schedule_id);
        $status = $request->status;


        if ($schedule) {
            $team = Team::find($schedule->team_id);
            $targetUser->shifts()->updateExistingPivot($shift->id, ['status' => $status]);
            $targetUser->shifts()->updateExistingPivot($shift->id, ['trade_user_id' => $request->trade_user_id]);
            $targetUser->shifts()->updateExistingPivot($shift->id, ['trade_shift_id' => $request->trade_shift_id]);

            $shiftusers = $this->shiftUsers($shift);
            $usershifts = $this->userShifts($targetUser);

            $data = [
                'shiftusers' => $shiftusers,
                'usershifts' => $usershifts,
                'trades' => $this->teamTrades($team)
            ];
        }


        return response()->json($data);

    }


        /**
     * 
     * Change a user's shift status
     *  - ROLE: 
     * 
     */
    public function approveAllRequests($id, $status)
    {
        $schedule = Schedule::find($id);
        $shifts = $schedule->shifts()->get();

        foreach ($shifts as $shift) {
          $shift->users()->updateExistingPivot($shift->users()->wherePivot('status', $status)->allRelatedIds(), ['status' => 2]);
        };

        $schedule = Schedule::with('shifts')->find($id);

        return response()->json($schedule);

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
          $shiftusers = $this->shiftUsers($shift);
        }

        return response()->json($shiftusers);
    }





    /**
     * 
     * Return all open trade requests
     *  - ROLE: team member
     * 
     */
    public function getTradeRequests($teamid)
    {
        $data = ['message' => 'Team Not Found'];
        $team = Team::find($teamid);

        if ($team) {
          $data = [
            'trades' => $this->teamTrades($team)
          ];
        }

        return response()->json($data);
    }

    
    /**
     * 
     * make trade
     *  - ROLE: team member
     * 
     */
    public function makeTrade($teamid, Request $request)
    {
        $data = ['message' => 'Team Not Found'];
        $user = Auth::user();
        $targetUser = User::find($request->user_id);
        $shift = Shift::find($request->shift_id);
        $team = $user->teams->find($teamid);


        if ($team) {
            $targetUser->shifts()->detach($shift->id);
            $user->shifts()->detach($shift->id);
            $user->shifts()->attach($shift->id);
            $user->shifts()->updateExistingPivot($shift->id, ['status' => 2]);

            $data = [
              'trades' => $this->teamTrades($team)
            ];
        }

        return response()->json($data);
    }


    
    /**
     * 
     * Show schedule, shift and trade statistics
     *  - ROLE: team member
     * 
     */
    public function showStatistics($teamid)
    {
        $data = ['message' => 'Team Not Found'];
        $user = Auth::user();
        $team = $user->teams->find($teamid);
        $stats = [];
        $totalShifts = 0;
        $totalSpots = 0;
        $availSpots = 0;
        $shiftWithNeeds = 0;
        $availTrades = 0;
        $shiftWithTrades = 0;

        if ($team) {

            // Available shift spots, shifts with needs
            $date = date_create(now())->modify('-7 days');

            $shifts = $team->shifts()
                        ->with('schedule')
                        ->withCount(['users', 'trades'])
                        ->where('schedules.date_start','>',$date)
                        ->where('schedules.status','>',0)
                        ->get();

            $totalShifts = $shifts->count();
            $totalSpots = $shifts->sum('max_participants');

            foreach($shifts as $shift) {

                $free = $shift->max_participants - $shift->users_count;
                if ($free > 0) {
                    $availSpots += $free;
                    $shiftWithNeeds += 1;
                }

                if ($shift->trades_count > 0) {
                    $availTrades += $shift->trades_count;
                    $shiftWithTrades += 1;
                }
            };

            $stats['total_spots'] = $totalSpots;
            $stats['available_spots'] = $availSpots;
            $stats['available_trades'] = $availTrades;
            $stats['total_shifts'] = $totalShifts;
            $stats['shifts_with_needs'] = $shiftWithNeeds;
            $stats['shifts_with_trades'] = $shiftWithTrades;

            



            $data = [
                'stats' => $stats
            ];
        }

        return response()->json($data);
    }


    /**
     * 
     * Show user's shifts for ALL TEAMS
     *  - ROLE: authenticated user
     * 
     *  GET -> /user/shifts
     * 
     */
    public function userAllShifts()
    {
        $user = Auth::user();
        $shifts = $this->userShifts($user);
        return response()->json($shifts);
    }


    /**
     * 
     * Show user's shifts for ALL TEAMS
     *  - ROLE: authenticated user
     * 
     *  GET -> /user/shifts
     * 
     */
    public function userTeamShifts(Request $request)
    {
      $date = date_create(now())->modify('-1 days');
      $user = User::find($request->user_id);
      $shifts = $user->shifts()
                      ->with(['schedule', 'location'])
                      ->whereHas('schedule', function($q) use($request) {
                        $q->where('team_id', '=', $request->team_id);
                      })
                      ->withPivot('status')
                      ->where('time_start','>',$date)
                      ->wherePivot('status','=',2)
                      ->orderBy('time_start')
                      ->get();

      return response()->json($shifts);
    }










    // Shared Shift Functions

    public function userShifts(User $user)
    {
      $date = date_create(now())->modify('-7 days');
      $shifts = [];

      if ($user) {
          $shifts = $user->shifts()
                      ->with('schedule', 'location', 'users')
                      ->where('time_start','>',$date)
                      ->wherePivot('status','!=','3')
                      ->orderBy('time_start')
                      ->get();
      }
      
      return $shifts;
    }


    public function shiftUsers(Shift $shift)
    {
      $user = Auth::user();
      $schedule = $user->schedules->find($shift->schedule_id);
      $shiftusers = [];

      if ($schedule) {
          $shiftusers = $shift->users()->get();
      }

      return $shiftusers;
    }


    public function teamTrades(Team $team)
    {
      $date = date_create(now())->modify('-7 days');
      $trades = $team->shifts()
                  ->with(['trades','schedule','location','users'])
                  ->whereHas('trades')
                  ->where('schedules.date_start','>',$date)
                  ->where('schedules.status','>',0)
                  ->get();


      return $trades;
    }

}
