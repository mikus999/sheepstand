<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Permission;
use App\Models\User;
use App\Models\Team;
use Auth;
use Helper;

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
        $user = $request->user();
        $roles = Helper::getUserRoles($user);

        $user['roles'] = $roles;

        return response()->json($user);
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
        $siteUsers = User::with('languages')->get();

        foreach($siteUsers as $key => $user) {
          $targetUser = User::find($user->id);
          $userRoles = $targetUser->getRoles();
          $siteUsers[$key]['site_roles'] = $userRoles;
        };
      }

      return response()->json($siteUsers);
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
      }

      $data = [
        'users' => $siteUsers
      ];
      
      return response()->json($data);
    }
}
