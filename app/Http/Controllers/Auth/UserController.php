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
        $user = Auth::user();
        $roles = Helper::getUserRoles($user);

        $user->roles = $roles;
        $user->user_availabilities = $user->user_availabilities()->get();
        $user->user_vacations = $user->user_vacations()->get();
        $user->marriage_mate = $user->marriage_mate()->first();

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
        $siteUsers = User::with('languages', 'marriage_mate')->get();

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



    public function updateFTSStatus(Request $request)
    {
      $user = Auth::user();
      $targetUser = $user;

      if ($request->user_id) {
        $targetUser = User::find($request->user_id);
      }

      $targetUser->fts_status = $request->status;
      $targetUser->save();

      return response()->json($targetUser);

    }





    public function updateMarriageMate(Request $request)
    {
      $user = Auth::user();
      $team = $user->teams->find($request->team_id);

      if (($team && $user->hasRole('team_admin', $team)) || $request->mate1_id == $user->id) {     

        $mate1User = User::find($request->mate1_id);
        $currentMate = $mate1User->mate_id;
        $mate1User->mate_id = $request->mate2_id ? $request->mate2_id : null;
        $mate1User->save();

        if ($request->mate2_id) {
          $newMate = User::find($request->mate2_id);
          $newMate->mate_id = $request->mate1_id;
          $newMate->save();
        }

        if ($currentMate) {
          $oldMate = User::find($currentMate);
          if ($oldMate) {
            $oldMate->mate_id = null;
            $oldMate->save();
          }
        }
        
      } else {
        $mate1User = $user;
      }


      return response()->json($mate1User);

    }
}
