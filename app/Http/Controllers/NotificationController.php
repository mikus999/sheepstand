<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\User;
use App\Models\NotificationSetting;
use DB;
use Helper;
use Auth;

class NotificationController extends Controller
{

    /**
     * 
     * Update Telegram details
     *  - ROLE: team_admin, super_admin
     * 
     */
    public function updateTelegram(Request $request, $id)
    {
      $user = Auth::user();
      $team = $user->teams()->find($id);
      $settings = $team->notificationsettings;

      if ($team) {
        if ($user->hasRole('team_admin', $team) || $user->hasRole('super_admin', null)) {
          $settings->telegram_channel_id = $request->channel_id;
          $settings->telegram_access_hash = $request->access_hash;
          $settings->save();
        }
      }

      return response()->json($settings);
    }
    /**
     * 
     * Get team notification settings
     *  - ROLE: team_admin, super_admin
     * 
     */
    public function notificationSettings($teamid) {

      $team = Team::find($teamid);
      $user = Auth::user();
      $error = false;
      $settings = [];

      if ($user->hasRole('team_admin', $team) || $user->hasRole('super_admin', null)) {
        $settings = $team->notificationsettings;

        if (empty($settings)) {
          $notification = NotificationSetting::create([
            'team_id' => $team->id,
            'telegram_channel_id' => '',
            'telegram_access_hash' => ''
          ]);

          $team = Team::find($teamid);
          $settings = $team->notificationsettings;
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
        'settings' => $settings,
      ];
    }

    return response()->json($data);
  }

}
