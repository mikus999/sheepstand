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
      $locations = Team::find($teamid)->locations;

      return response()->json($locations);
    }




    public function store($teamid, Request $request)
    {
        $location = Location::create([
            'team_id' => $teamid,
            'name' => $request->name,
            'color_code' => $request->color_code,
            'map' => $request->map,
            'default' => $request->default
        ]);

        $data = [
            'data' => $location,
            'status' => (bool) $location,
            'message' => $location ? 'Location Created!' : 'Error Creating Location',
        ];

        return response()->json($data);
    }



    public function show($teamid, $locid)
    {
        $location = Location::find($locid);
        return response()->json($location);
    }




    public function update($teamid, Request $request, $locid)
    {
        $location = Location::find($locid);
        $location->name = $request->name;
        $location->color_code = $request->color_code;
        $location->map = $request->map;
        $location->save();

        return response()->json($location);
    }



    public function destroy($teamid, $locid)
    {
      Location::destroy($locid);
      $data = [
          'message' => 'Location Deleted!',
      ];
      return response()->json($data);
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
