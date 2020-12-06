<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Helper;
use App\Models\User;
use App\Models\UserAvailability;
use Auth;
use DB;

class UserAvailabilityController extends Controller
{

  public function setDefaultAvailability(Request $request)
  {
    $user = Auth::user();
    $default = $request->default || null;

    if ($request->user_id) {
      $targetUser = User::find($request->user_id);
    } else {
      $targetUser = $user;
    }

    //$helper = new Helper;
    Helper::addDefaultAvailability($targetUser, $default);

    return response()->json($targetUser->user_availabilities);
  }


  public function setAvailability(Request $request)
  {
    $user = Auth::user();
    $availability = json_decode($request->availability);
    $data = [];


    if ($request->user_id) {
      $targetUser = User::find($request->user_id);
    } else {
      $targetUser = $user;
    }


    foreach ($availability as $period) {
      $temp = [
        'user_id' => $targetUser->id, 
        'day_of_week' => $period->day_of_week, 
        'start_time' => $period->start_time, 
        'end_time' => $period->end_time,
        'available' => $period->available
      ];
      $data[] = $temp;
    }

    UserAvailability::upsert($data, ['user_id', 'day_of_week', 'start_time', 'end_time'], ['available']);

    return response()->json($targetUser->user_availabilities);
  }


  public function getAvailability()
  {
    $user = Auth::user();
    return response()->json($user->user_availabilities);
  }
}
