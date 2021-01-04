<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Helper;
use App\Models\User;
use App\Models\Team;
use App\Models\UserAvailability;
use App\Models\UserVacation;
use Auth;
use DB;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;

class UserAvailabilityController extends Controller
{

  public function setDefaultAvailability(Request $request)
  {
    $user = Auth::user();
    $default = $request->default || null;

    if ($request->user_id) {
      if (!$user->hasRole('super_admin', null)) return RB::error(403); // Access denied

      $targetUser = User::find($request->user_id);
    } else {
      $targetUser = $user;
    }

    //$helper = new Helper;
    Helper::addDefaultAvailability($targetUser, $default);

    return RB::success(['availability' => $targetUser->user_availabilities]);
  }


  public function getAllAvailability($teamid)
  {
    $user = Auth::user();
    $team = Team::find($teamid);
    
    if (!$team) return RB::error(404);

    if (($user->hasRole(['elder','team_admin'], $team) || $user->hasRole('super_admin', null))) {
      return RB::success(['users' => $team->user_availability()->get()]);
    } else {
      return RB::error(403); // Access denied
    }


  }


  public function setAvailability(Request $request)
  {
    $user = Auth::user();
    $availability = json_decode($request->availability);
    $data = [];


    if ($request->user_id) {
      if (!$user->hasRole('super_admin', null)) return RB::error(403); // Access denied

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

    return RB::success(['availability' => $targetUser->user_availabilities]);
  }


  public function getAvailability()
  {
    $user = Auth::user();
    return RB::success(['availability' => $user->user_availabilities]);
  }



  public function getVacation()
  {
    $user = Auth::user();
    return RB::success(['vacation' => $user->user_vacations]);
  }


  public function setVacation(Request $request)
  {
    $user = Auth::user();

    UserVacation::create([
      'user_id' => $user->id,
      'date_start' => $request->date_start,
      'date_end' => $request->date_end,
      'note' => $request->note
    ]);

    return RB::success(['vacation' => $user->user_vacations]);
  }


  public function deleteVacation($id)
  {
    $user = Auth::user();
    $found = $user->user_vacations()->find($id);

    if ($found) {
      UserVacation::destroy($id);
      return RB::success(['vacation' => $user->user_vacations]);
    } else {
      return RB::error(404);
    }

  }
}
