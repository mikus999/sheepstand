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


}
