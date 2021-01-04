<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use Helper;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;

class UserController extends Controller
{
    /**
     * Get authenticated user.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function current(Request $request)
    {
        $user = Auth::user();

        if ($user) {
          $roles = Helper::getUserRoles($user);

          $user->roles = $roles;
          $user->user_availabilities = $user->user_availabilities()->get();
          $user->user_vacations = $user->user_vacations()->get();
          $user->marriage_mate = $user->marriage_mate()->first();
  
          return RB::success(['user' => $user]);

        } else {
          return RB::error(401);
        }

    }



    /**
     * Get all site users.
     *   Role: super_admin ONLY
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllUsers()
    {
      $user = Auth::user();
      $siteUsers = [];

      if ($user->hasRole('super_admin', null)) {
        $siteUsers = User::with('languages', 'marriage_mate')->get();

        foreach($siteUsers as $key => $user) {
          $targetUser = User::find($user->id);
          $userRoles = $targetUser->getRoles();
          $siteUsers[$key]['site_roles'] = $userRoles;
        };

        return RB::success(['users' => $siteUsers]);
      } else {
        return RB::error(403); // Access denied
      }

    }


    /**
     * Get all site users for given role.
     *   Role: super_admin ONLY
     *
     * @param String $role || GLOBAL ROLES ONLY
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUsersByRole($role)
    {
      $user = Auth::user();
      $siteUsers = [];

      if ($user->hasRole('super_admin', null)) {
        $siteUsers = User::whereRoleIs($role)->with('languages')->get();
        return RB::success(['users' => $siteUsers]);

      } else {
        return RB::error(403); // Access denied
      }

    }

}
