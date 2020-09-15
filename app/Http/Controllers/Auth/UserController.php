<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\User;
use Auth;

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
        return response()->json($request->user());
    }


    // GET
    public function getPermissions(Request $request)
    {
      if ($request->user_id) {
        $user = User::find($request->user_id);
      } else {
        $user = Auth::user();
      }

        return response()->json($user->getAllPermissions());
    }

    // POST
    public function setPermissions(Request $request)
    {
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

            $message = $user->getAllPermissions();

        } else {
          $message = 'Permission not found';
        }

        return response()->json($message);
    }

    // POST
    public function setRoles(Request $request)
    {
        $user = User::find($request->user_id);
        $role = $request->role;
        $changetype = $request->changetype;
        $message = '';

        if (!is_null(Role::where('name', $role)->first())) {
            if ($changetype == 'add') {
              $user->assignRole($role);
            } elseif ($changetype == 'remove') {
              $user->removeRole($role);
            } elseif ($changetype == 'sync') {
              $user->syncRoles($role);
            }

            $message = $user->getAllPermissions();

        } else {
            $message = 'Role not found';
        }

        return response()->json($message);
    }
}
