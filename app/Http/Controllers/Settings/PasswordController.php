<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        //$user = $request->user;

        $this->validate($request, [
            'password' => 'required|confirmed|min:6',
        ]);

        return response()->json($request);
        /*
        $user->update([
            'password' => bcrypt($request->password),
        ]);
        */
    }
}
