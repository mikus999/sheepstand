<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Team;
use App\User;
use App\Location;
use Helper;
use Auth;

class TeamController extends Controller
{

    public function index()
    {
      $user = Auth::user();
      $teams = User::find($user->id)->teams;

      $data = [
          'teams' => $user->teams
      ];
      return response()->json($data);
    }




    public function store(Request $request)
    {
      $user = Auth::user();
      $userid = $user->id;
      $teamcode = Helper::getUniqueCode(6, 'TM-');

      $newteam = Team::create([
          'name' => $request->name,
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

      $user->teams()->attach($newteam);

      $data = [
          'data' => $newteam,
          'status' => (bool) $newteam,
          'teamcode' => $teamcode,
          'message' => $newteam ? 'Team Created!' : 'ERROR',
      ];

      return response()->json($data);
    }




    public function show($id)
    {
        $team = Team::find($id);
        return response()->json($team);
    }



    public function update(Request $request, $id)
    {
      $team = Team::find($id);
      $team->name = $request->name;
      if ($request->newcode = true) {
        $team->code = Helper::getUniqueCode(6, 'TM-');
      }
      $team->user_id = $request->user_id;
      $team->save();

      return response()->json($team);
    }



    public function destroy($id)
    {
        Team::destroy($id);
        $data = [
            'message' => 'Team Deleted!',
        ];
        return response()->json($data);

    }




    // Custom Functions

    public function addUserToTeam(Request $request)
    {
      $error = false;

      if ($request->user_id) {
        $user = User::find($request->user_id);
      } elseif ($request->user_code) {
        $user = User::where('user_code',$request->user_code)->first();
      }

      if ($user) {
        if (strlen($request->team_id)<7) {
          $teamid = $request->team_id;
          $team = Team::find($teamid);
        } else {
          $team = Team::where('code',$request->team_id)->first();
        }

        if ($team) {
          $user->teams()->detach($team); // First detach if already exists
          $user->teams()->attach($team);
          $message = 'SUCCESS';
        } else {
          $error = true;
          $message = 'NOT_FOUND';
        }
      } else {
        $error = true;
        $message = 'USER NOT FOUND';
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
          'teams' => $user->teams,
          'user' => $user
        ];
      }
      return response()->json($data);
    }


    public function removeUserFromTeam(Request $request)
    {
      $userid = $request->user_id;
      $teamid = $request->team_id;

      $user = User::find($userid);
      $user->teams()->detach($teamid);

      $data = [
          'users' => 'Successfully left team!',
          'teams' => $user->teams
      ];
      return response()->json($data);
    }


    public function changeTeamCode($id)
    {
      $team = Team::find($id);
      $teamcode = Helper::getUniqueCode(6, 'TM-');
      $team->code = $teamcode;
      $team->save();

      return response()->json($team);
    }


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


    public function getTeamUsers($id)
    {
      $team = Team::find($id);

      return response()->json($team->users);
    }


    public function updateSetting(Request $request)
    {
      $teamid = $request->team_id;
      $setting = $request->setting; // must match DB column name
      $value = $request->value;

      $team = Team::find($teamid);
      $team->$setting = $value;
      $team->save();

      return response()->json($team);
    }
}
