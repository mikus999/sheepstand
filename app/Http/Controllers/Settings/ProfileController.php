<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use Helper;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;

class ProfileController extends Controller
{
    /**
     * Update the user's profile information.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$user->id,
        ]);

        $data = tap($user)->update($request->only('name', 'email'));

        return RB::success(['user' => $data]);
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

      return RB::success(['user' => $targetUser]);

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

        return RB::success(['user' => $mate1User]);

      } else {
        return RB::error(403); // Access denied
      }

    }
  


    public function updateDriverStatus(Request $request)
    {
      $user = Auth::user();
      $targetUser = $user;

      if ($request->user_id) {
        $targetUser = User::find($request->user_id);
      }

      $targetUser->driver = $request->status;
      $targetUser->save();

      return RB::success(['user' => $targetUser]);

    }


}
