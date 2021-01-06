<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\User;
use App\Models\Location;
use App\File;
use Helper;
use Auth;
use DB;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;


class LocationController extends Controller
{
    /*
    API Usage
    index   ->  GET	    /teams/{team_id}/locations
    store   ->  POST    /teams/{team_id}/locations ('name','color_code', 'map', 'default')
    show    ->  GET	    /teams/{team_id}/locations/{location_id}
    update  ->  PATCH	  /teams/{team_id}/locations/{location_id} ('name','color_code', 'map')
    destroy ->  DELETE	/teams/{team_id}/locations/{location_id}
    */


    public function index($teamid)
    {
      $user = Auth::user();
      $team = $user->teams->find($teamid);

      if ($team) {
        return RB::success(['locations' => $team->locations]);
      } else {
        return RB::error(404); // team not found
      }
    }




    public function store($teamid, Request $request)
    {
      $user = Auth::user();
      $team = $user->teams->find($teamid);

      if ($team) {

        if ($user->hasRole('team_admin', $team)) {
          $location = Location::create([
            'team_id' => $teamid,
            'name' => $request->name,
            'color_code' => $request->color_code,
            'map' => $request->map,
            'default' => $request->default
          ]);

          return RB::success(['location' => $location]);

        } else {
          return RB::error(403); // Access denied
        }
      } else {
        return RB::error(404); // team not found
      }
    }



    public function show($teamid, $locid)
    {
      $user = Auth::user();
      $team = $user->teams->find($teamid);

      if ($team) {
        $location = $team->locations->find($locid);
        if (!$location) return RB::error(404); // location not found

        return RB::success(['location' => $location]);
      } else {
        return RB::error(404); // team not found
      }
    }




    public function update($teamid, Request $request, $locid)
    {
      $user = Auth::user();
      $team = $user->teams->find($teamid);

      if ($team) {
        if ($user->hasRole('team_admin', $team)) {
          $location = $team->locations->find($locid);
          if (!$location) return RB::error(404); // location not found

          $location->name = $request->name;
          $location->color_code = $request->color_code;
          $location->map = $request->map;
          $location->save();

          return RB::success(['location' => $location]);

        } else {
          return RB::error(403); // Access denied
        }
      } else {
        return RB::error(404); // team not found
      }
    }



    public function destroy($teamid, $locid)
    {
      $user = Auth::user();
      $team = $user->teams->find($teamid);

      if ($team) {
        if ($user->hasRole('team_admin', $team)) {
          $location = $team->locations->find($locid);
          if (!$location) return RB::error(404); // location not found

          Location::destroy($locid);
          return RB::success();

        } else {
          return RB::error(403); // Access denied
        }
      } else {
        return RB::error(404); // team not found
      }
    }


    public function setDefault($teamid, Request $request, $locid)
    {
      $user = Auth::user();
      $team = Team::find($teamid);

      if ($team) {
        if ($user->hasRole('team_admin', $team)) {

          // Set 'default' for all team locations to 'false'
          $affected = DB::table('locations')->where('team_id', '=', $teamid)->update(array('default' => false));

          // Set the selected location 'default' to 'true'
          $location = $team->locations->find($locid);

          if ($location) {
            $location->default = true;
            $location->save();

            return RB::success(['location' => $location]);

          } else {
            return RB::error(404); // location not found
          }

        } else {
          return RB::error(403); // access denied
        }

      } else {
        return RB::error(404); // team not found
      }

    }
}
