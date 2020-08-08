<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Team;
use App\User;
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
      $userid = $request->user_id;
      $user = User::find($userid);

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
        $message = 'NOT_FOUND';
      }


      $data = [
          'message' => $message,
          'teams' => $user->teams
      ];
      return response()->json($data);
    }


    public function removeUserFromTeam(Request $request)
    {
      $userid = $request->user_id;
      $teamid = $request->team_id;

      $user = User::find($userid);
      $user->teams()->detach($teamid);

      $data = [
          'message' => 'Successfully left team!',
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
        $message = 'SUCCESS';

        // Find team contact
        $user = User::find($team->user_id);
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
}
