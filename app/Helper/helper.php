<?php

namespace App\Helper;

use DB;

class Helper
{
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
}


?>
