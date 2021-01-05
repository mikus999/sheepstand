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
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;

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
      return RB::success(['teams' => $user->teams()->with('notificationsettings')->get()]);
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
      $teamcode = Helper::getUniqueCode(6, 'team_code');
      $teamUUID = Helper::getUniqueCode(12, 'team_name');

      if ($request->display_name == null) return RB::error(400); // Bad request, team name is null

      $newteam = Team::create([
          'name' => $teamUUID,
          'display_name' => $request->display_name,
          'code' => $teamcode,
          'user_id' => $userid
      ]);

      $location = Location::create([
        'team_id' => $newteam->id,
        'name' => 'Default Location',
        'color_code' => '#AEAEAE',
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


      return RB::success(['team' => $newteam]);

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
      
      if ($team) {
        return RB::success(['team' => $team]);
      } else {
        return RB::error(404); // team not found
      }
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
            $team->code = Helper::getUniqueCode(6, 'team_code');
          }
          $team->user_id = $request->user_id;
          $team->save();
          
          return RB::success(['team' => $team]);

        } else {
          return RB::error(403); // access denied
        }
      } else {
        return RB::error(404); // team not found
      }

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

        if ($team) {
          if ($user->hasRole('team_admin', $team)) {
            Team::destroy($id);
            return RB::success();
          } else {
            return RB::error(403); // access denied
          }

        } else {
          return RB::error(404); // team not found
        }

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

      
      if (strlen($request->team_id)<6) {
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

      if (!$targetUser) return RB::error(404);
      if (!$team) return RB::error(404);


      // Check if the user is adding himself to a team.
      // If not, only Team Admins and Super Admins can add/remove other users to/from a team
      if ($selfadd || ($user->hasRole('team_admin', $team) || $user->hasRole('super_admin', null))) {

        $targetUser->teams()->detach($team); // First detach if already exists
        $targetUser->teams()->attach($team);
        $targetUser->attachRole('publisher', $team);


        $data = [
          'team' => $team,
          'teams' => $targetUser->teams,
          'user' => $targetUser
        ];

        return RB::success($data);

      } else {
        return RB::error(403); // Access denied
      }

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
      $selfadd = false;

      if ($request->user_id) {
        $targetUser = User::find($userid);
      } else {
        $targetUser = $user;
        $selfadd = true;
      }


      if (!$targetUser) return RB::error(404);
      if (!$team) return RB::error(404); // Bad Request; team not found

      // Only Team Admins and Super Admins can add/remove users from a team
      if ($selfadd || ($user->hasRole('team_admin', $team) || $user->hasRole('super_admin', null))) {
        $targetUser->teams()->detach($teamid);
        
        // Remove user's shifts
        DB::table('shift_user')
          ->whereIn('shift_id', $team->shifts->pluck('id'))
          ->where('user_id', $userid)
          ->delete();

        return RB::success(['teams' => $targetUser->teams]);

      } else {
        return RB::error(403); // Access denied
      }


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

      if (!$team) return RB::error(404); // Bad Request; team not found


      if ($user->hasRole('team_admin', $team) || $user->hasRole('super_admin', null)) {
        $teamcode = Helper::getUniqueCode(6, 'team_code');
        $team->code = $teamcode;
        $team->save();

        return RB::success(['team' => $team]);
      } else {
        return RB::error(403); // Access denied
      }
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
        $data = [
          'team' => $team,
          'user' => User::find($team->user_id)// Find team contact
        ];
        return RB::success($data);

      } else {
        return RB::error(404); // Bad Request; team not found
      }

    }


    /**
     * 
     * Return all team users
     *  - ROLE: team member
     * 
     */
    public function getTeamUsers($id)
    {
      $user = Auth::user();
      $team = $user->teams->find($id);
      $teamUsers = [];

      if ($team) {
        $teamUsers = $team->users()->with('shifts')->get();

        foreach($teamUsers as $key => $user) {
          $targetUser = User::find($user->id);
          $userRoles = $targetUser->getRoles($team);
          $teamUsers[$key]['team_role'] = count($userRoles) > 0 ? $userRoles[0] : '';
        };

        return RB::success(['users' => $teamUsers]);

      } else {
        return RB::error(404); // Bad request
      }
      
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

      if (!$team) return RB::error(404); // Bad Request; team not found

      if ($user->hasRole(['team_admin'], $team) || $user->hasRole('super_admin', null)) {
        $team->$setting = $value;
        $team->save();

        return RB::success(['team' => $team]);
        
      } else {
        return RB::error(403); // Access denied
      }


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
      $teamid = $request->team_id;

      // Set 'default' for all teams to 'false'
      $affected = DB::table('team_user')->where('user_id', '=', $userid)->update(['default_team' => false]);

      // Set the selected team 'default' to 'true'
      $affected = DB::table('team_user')
                    ->where([
                      ['user_id', '=', $userid],
                      ['team_id', '=', $teamid],
                    ])
                    ->update(['default_team' => true]);

      return RB::success(['affected_rows' => $affected]);
    }
}
