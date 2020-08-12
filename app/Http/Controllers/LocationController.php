<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Team;
use App\User;
use App\Location;
use Helper;
use Auth;


class LocationController extends Controller
{
    /*
    API Usage
    index   ->  GET	    /teams/{team_id}/locations
    store   ->  POST    /teams/{team_id}/locations ('name','color_code', 'map')
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
            'map' => $request->map
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
}