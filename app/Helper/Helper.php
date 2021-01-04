<?php

namespace App\Helper;

use DB;
use Auth;
use App\Models\User;
use App\Models\UserAvailability;

class Helper
{
    public static function getUserRoles($user) {

      $teams = $user->teams;
      $roles_global = $user->getRoles();
      $roles_team = [];

      if ($teams) {
        foreach ($teams as $team) {
          $teamid = $team->id;
          $roles_team = $user->getRoles($team);
          $roles[$teamid] = $roles_team;
        }
      }
      $roles['global'] = $roles_global;


      return collect($roles);
    }


    public static function getUniqueCode($digits, $scope, $prefix = '', $suffix = '') {
        $returnstr = '';
        $is_unique = false;

        //do {
          $rangestart = str_pad(1,$digits,0,STR_PAD_RIGHT);
          $rangeend = str_pad(9,$digits,9,STR_PAD_RIGHT);
          $randomnum = random_int($rangestart, $rangeend);
          $returnstr = $randomnum;

          // If the prefix variable is specified, append it to beginning of string
          if (!empty($prefix)) {
              $returnstr = $prefix.$randomnum;
          }

          // If the suffix variable is specified, append it to end of string
          if (!empty($suffix)) {
              $returnstr = $randomnum.$suffix;
          }

          $is_unique = Helper::checkUniqueCode($returnstr, $scope);

        //} while ($is_unique);


        return $returnstr;
    }

    public static function checkUniqueCode($code, $scope) {
      // Generate usercode and check for uniqueness
      $uniq = false;

      if ($scope === 'user') {
        $uniq = !is_null(DB::table('users')->where("user_code",$code)->first());
      } elseif ($scope === 'team_code') {
        $uniq = !is_null(DB::table('teams')->where("code",$code)->first());
      } elseif ($scope === 'team_name') {
        $uniq = !is_null(DB::table('teams')->where("name",$code)->first());
      }

      return $uniq;
    }


    public static function addDefaultAvailability(User $user, $available)
    {
      $data = [];

      for ($d = 1; $d <= 7; $d++) {
        for ($h = 0; $h <= 23; $h++) {
          $temp = [
            'user_id' => $user->id, 
            'day_of_week' => $d, 
            'start_time' => $h . ':00', 
            'end_time' => ($h+1) . ':00',
            'available' => $available
          ];

          $data[] = $temp;
        }
      }

      UserAvailability::upsert($data, ['user_id', 'day_of_week', 'start_time', 'end_time'], ['available']);
    }

}


?>
