<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\User;
use App\Models\Location;
use App\Models\NotificationSetting;
use DB;
use Helper;
use Auth;

class TeamController extends Controller
{

    /**
     * 
     * Return details of all teams user is member of
     * 
     */
    public function index()
    {
      $user = Auth::user();

      $data = [
          'teams' => $user->teams()->with('notificationsettings')->get()
      ];
      return response()->json($data);
    }



    /**
     * 
     *  Create a new team
     * 
     */
    public function store(Request $request)
    {
      $user = Auth::user();
      $userid = $user->id;
      $teamcode = Helper::getUniqueCode(6, 'team_code', 'TM-');
      $teamUUID = Helper::getUniqueCode(12, 'team_name');

      $newteam = Team::create([
          'name' => $teamUUID,
          'display_name' => $request->display_name,
          'code' => $teamcode,
          'user_id' => $userid
      ]);

      $location = Location::create([
        'team_id' => $newteam->id,
        'name' => 'Default Location',
        'color_code' => '#000000',
        'map' => null,
        'default' => true
      ]);

      $notification = NotificationSetting::create([
        'team_id' => $newteam->id,
        'telegram_channel_id' => '',
        'telegram_access_hash' => ''
      ]);

      $user->teams()->attach($newteam);


      // ADD 'TEAM ADMIN' ROLE TO USER FOR THIS TEAM
      $user->attachRole('team_admin', $newteam);


      $data = [
          'team' => $newteam,
          'status' => (bool) $newteam,
          'teamcode' => $teamcode,
          'message' => $newteam ? 'Team Created!' : 'ERROR',
      ];

      return response()->json($data);
    }



    /**
     * 
     * Return team details, only if user is member
     * 
     */
    public function show($id)
    {
        $user = Auth::user();
        $team = $user->teams()->with('notificationsettings')->find($id);
        return response()->json($team);
    }



    /**
     * 
     * Update team details
     *  - ROLE: team_admin
     * 
     */
    public function update(Request $request, $id)
    {
      $user = Auth::user();
      $team = $user->teams()->find($id);

      if ($team) {
        if ($user->hasRole('team_admin', $team)) {
          $team->display_name = $request->display_name;
          if ($request->newcode = true) {
            $team->code = Helper::getUniqueCode(6, 'team_code', 'TM-');
          }
          $team->user_id = $request->user_id;
          $team->save();
        }
      }

      return response()->json($team);
    }


    /**
     * 
     * Destroy/delete team,
     *  - ROLE: team_admin
     * 
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $team = $user->teams->find($id);
        $message = 'Access Denied';

        if ($team) {
          if ($user->hasRole('team_admin', $team)) {
            Team::destroy($id);
            $message = 'Team Deleted';
          }
        }

        $data = [
          'message' => $message,
        ];

        return response()->json($data);

    }






    /**
     * 
     * Add a user to a team
     *  - ROLE: team_admin, super_admin
     * 
     */
    public function addUserToTeam(Request $request)
    {
      $error = false;
      $selfadd = false;
      $user = Auth::user();

      if (strlen($request->team_id)<7) {
        $teamid = $request->team_id;
        $team = Team::find($teamid);
      } else {
        $team = Team::where('code',$request->team_id)->first();
      }


      // Find the target user
      if ($request->user_id) {
        $targetUser = User::find($request->user_id);
      } elseif ($request->user_code) {
        $targetUser = User::where('user_code',$request->user_code)->first();
      } else {
        $targetUser = $user;
        $selfadd = true;
      }


      // Check if the user is adding himself to a team.
      // If not, only Team Admins and Super Admins can add/remove other users to/from a team
      if ($selfadd || ($user->hasRole('team_admin', $team) || $user->hasRole('super_admin', null))) {

        // If the target user was found
        if ($targetUser) {
          if ($team) {
            $targetUser->teams()->detach($team); // First detach if already exists
            $targetUser->teams()->attach($team);
            $targetUser->attachRole('publisher', $team);
            $message = 'USER ADDED TO TEAM';
          } else {
            $error = true;
            $message = 'TEAM NOT FOUND';
          }
        } else {
          $error = true;
          $message = 'USER NOT FOUND';
        }
      } else {
        $error = true;
        $message = 'Access Denied';
      }


      if ($error) {
        $data = [
          'error' => $error,
          'message' => $message
        ];
      } else {
        $data = [
          'error' => $error,
          'message' => $message,
          'team' => $team,
          'teams' => $targetUser->teams,
          'user' => $targetUser
        ];
      }
      return response()->json($data);
    }



    /**
     * 
     * Remove a user from a team
     *  - ROLE: team_admin, super_admin
     * 
     */
    public function removeUserFromTeam(Request $request)
    {
      $userid = $request->user_id;
      $teamid = $request->team_id;
      $user = Auth::user();
      $team = Team::find($teamid);
      $targetUser = User::find($userid);


      // Only Team Admins and Super Admins can add/remove users from a team
      if ($user->hasRole('team_admin', $team) || $user->hasRole('super_admin', null)) {
        $targetUser->teams()->detach($teamid);
        
        // Remove user's shifts
        DB::table('shift_user')
          ->whereIn('shift_id', $team->shifts->pluck('id'))
          ->where('user_id', $userid)
          ->delete();
      }

      $data = [
          'users' => 'Successfully left team!',
          'teams' => $targetUser->teams
      ];
      return response()->json($data);
    }




    /**
     * 
     * Change the team code
     *  - ROLE: team_admin, super_admin
     * 
     */
    public function changeTeamCode($id)
    {
      $user = Auth::user();
      $team = Team::find($id);

      if ($user->hasRole('team_admin', $team) || $user->hasRole('super_admin', null)) {
        $teamcode = Helper::getUniqueCode(6, 'team_code', 'TM-');
        $team->code = $teamcode;
        $team->save();
      }

      return response()->json($team);
    }



    /**
     * 
     * Find a team by the given code
     * 
     */
    public function findTeamByCode($code) {
      $team = Team::where('code',$code)->first();
      $user = [];

      if ($team) {
        // Find team contact
        $user = User::find($team->user_id);
        $message = 'SUCCESS';
      } else {
        $message = 'NOT_FOUND';
      }

      $data = [
          'message' => $message,
          'team' => $team,
          'user' => $user
      ];
      return response()->json($data);
    }


    /**
     * 
     * Return all team users
     *  - ROLE: elder, team_admin, super_admin
     * 
     */
    public function getTeamUsers($id)
    {
      $user = Auth::user();
      $team = Team::find($id);
      $teamUsers = [];

      if ($user->hasRole(['elder','team_admin'], $team) || $user->hasRole('super_admin', null)) {
        $teamUsers = $team->users;

        foreach($teamUsers as $key => $user) {
          $targetUser = User::find($user->id);
          $userRoles = $targetUser->getRoles($team);
          $teamUsers[$key]['team_role'] = count($userRoles) > 0 ? $userRoles[0] : '';
        };
    
      }
      
      return response()->json($teamUsers);
    }



    /**
     * 
     * Change a team setting
     *  - ROLE: team_admin, super_admin
     * 
     */
    public function updateSetting(Request $request)
    {
      $teamid = $request->team_id;
      $setting = $request->setting; // must match DB column name
      $value = $request->value;

      $user = Auth::user();
      $team = Team::with('notificationsettings')->find($teamid);

      if ($user->hasRole(['elder','team_admin'], $team) || $user->hasRole('super_admin', null)) {
        $team->$setting = $value;
        $team->save();
      }

      return response()->json($team);
    }



    /**
     * 
     * Set the user's default team
     * 
     */
    public function setDefault(Request $request)
    {
      $user = Auth::user();
      $userid = $user->id;
      $teamid = $request->teamid;

      // Set 'default' for all team locations to 'false'
      $affected = DB::table('team_user')->where('user_id', '=', $userid)->update(['default_team' => false]);

      // Set the selected location 'default' to 'true'
      $affected = DB::table('team_user')
                    ->where([
                      ['user_id', '=', $userid],
                      ['team_id', '=', $teamid],
                    ])
                    ->update(['default_team' => true]);


      return response()->json($affected);
    }
}
