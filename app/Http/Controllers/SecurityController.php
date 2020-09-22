<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use App\Models\Team;
use Auth;

class SecurityController extends Controller
{

    /**
     * 
     * Return all Roles with their assigned Permissions
     * 
     * GET
     */
    public function getRolesWithPermissions()
    {
        $roles = Role::with('permissions')->get();

        return response()->json($roles);

    }


    // POST
    public function getRoles(Request $request)
    {
        if ($request->user_id) {
        $user = User::find($request->user_id);
        } else {
        $user = Auth::user();
        }

        $roles_team = [];
        $roles_global = [];

        if ($request->team_id) {
        $team = Team::find($request->team_id);
        if ($team) {
            $roles_team = $user->getRoles($team);
        }
        }

        $roles_global = $user->getRoles();
        $roles = array_merge($roles_team, $roles_global);

        $data = [
        'roles' => $roles
        ];

        return response()->json($data);
    }



    // POST
    public function setRoles(Request $request)
    {
        $user = User::find($request->user_id);
        $role = $request->role;
        $changetype = $request->changetype;
        $teamScope = $request->team_id !== null;
        $message = '';
        $team = [];
        $roles_team = [];
        $roles_global = [];

        if ($teamScope) {
          $team = Team::find($request->team_id);
        }

        if (($teamScope && $team) || !$teamScope) {
          if (!is_null(Role::where('name', $role)->first())) {
            $roleObj = Role::where('name', $role)->first();

            if ($changetype === 'add') {
              $user->attachRole($roleObj, $teamScope ? $team : null);
            } elseif ($changetype === 'remove') {
              $user->detachRole($roleObj, $teamScope ? $team : null);
            } elseif ($changetype === 'sync') {
              $user->syncRoles([$roleObj], $teamScope ? $team : null);
            }

            if ($team) {
              $roles_team = $user->getRoles($team);
            }
          }

          $roles_global = $user->getRoles();
          $roles = array_merge($roles_team, $roles_global);


          $data = [
            'roles' => $roles
          ];

        } else {
            $data = 'Role not found';
        }

        return response()->json($data);
    }



    // POST
    public function setPermissions(Request $request)
    {
      /*
       * NOT USING PERMISSIONS. USE ROLES INSTEAD.
       * 
       * 
        $user = User::find($request->user_id);
        $permission = $request->permission;
        $changetype = $request->changetype;
        $message = '';

        if (!is_null(Permission::where('name', $permission)->first())) {
            if ($changetype == 'add') {
              $user->givePermissionTo($permission);
            } elseif ($changetype == 'remove') {
              $user->revokePermissionTo($permission);
            } elseif ($changetype == 'sync') {
              $user->syncPermissions($permission);
            }

            $data = [
              'roles' => [],
              'permissions' => $user->allPermissions(),
            ];

        } else {
          $data = 'Permission not found';
        }

        return response()->json($data);
        */
    }
}
