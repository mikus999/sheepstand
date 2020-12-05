<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Helper;
use App\Models\User;
use Auth;
use DB;

class UserAvailabilityController extends Controller
{

  public function setAvailability(Request $request)
  {
    $user = Auth::user();

    if ($request->user_id) {
      $targetUser = User::find($request->user_id);
    } else {
      $targetUser = $user;
    }

    //$helper = new Helper;
    Helper::addDefaultAvailability($targetUser);

    return response()->json($targetUser->user_availabilities);
  }


  public function getAvailability()
  {
    $user = Auth::user();
    return response()->json($user->user_availabilities);
  }
}
