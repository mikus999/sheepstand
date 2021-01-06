<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use App\Models\Team;
use DB;
use Helper;
use Auth;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;


class MessageController extends Controller
{
    use \App\Traits\MessageTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $data = [
        'received' => $this->getMessages(),
        'sent' => $this->getSentMessages()
      ];

      return RB::success($data);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $user = Auth::user();
      $access_allowed = false;

      if ($request->sender_type == 'App\Models\Team') {
        $team = Team::find($request->sender_id);
        $access_allowed = $user->hasRole('team_admin', $team);
      } else if ($request->sender_type == 'App\Models\User') {
        $access_allowed = $user->id == $request->sender_id;
      }

      if ($access_allowed || $user->hasRole('super_admin', null)) {

        $alert = Message::create([
          'sender_id' => $request->sender_id,
          'sender_type' => $request->sender_type,
          'recipient_id' => $request->recipient_id,
          'recipient_type' => $request->recipient_type,
          'message_subject' => $request->message_subject,
          'message_body' => $request->message_body,
          'named_route' => $request->named_route,
          'color' => $request->color,
          'icon' => $request->icon,
          'show_banner' => $request->show_banner,
          'expires_on' => $request->expires_on
        ]);

        $data = [
          'received' => $this->getMessages(),
          'sent' => $this->getSentMessages()
        ];

        return RB::success($data);

      } else {
        return RB::error(403); // access denied
      }

    }



    /**
     * Remove the specified resource from storage.
     *
     * @param  Integer  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      $user = Auth::user();
      $message = Message::find($id);
      $access_allowed = $user->hasRole('super_admin', null);

      if ($message) {
        if (!$access_allowed) {
          if ($message->sender_type == 'App\Models\Team') {
            $team = Team::find($message->sender_id);
            $access_allowed = $user->hasRole('team_admin', $team);
          } else if ($message->sender_type == 'App\Models\User') {
            $access_allowed = $message->sender_id == $user->id;
          }
        }

        if ($access_allowed) {
          Message::destroy($id);

          $data = [
            'received' => $this->getMessages(),
            'sent' => $this->getSentMessages()
          ];

          return RB::success($data);

        } else {
          return RB::error(403); // access denied
        }
      } else {
        return RB::error(404); // message not found
      }

    }


    public function markAsRead($id)
    {
      $user = Auth::user();
      
      if (!Message::find($id)) return RB::error(404); // message not found

      DB::table('message_user')->updateOrInsert(
        ['user_id' => $user->id, 'message_id' => $id],
        ['read' => true]
      );

      $data = [
        'received' => $this->getMessages(),
        'sent' => $this->getSentMessages()
      ];

      return RB::success($data);
    }


    public function markAsUnread($id)
    {
      $user = Auth::user();

      if (!Message::find($id)) return RB::error(404); // message not found

      DB::table('message_user')->updateOrInsert(
        ['user_id' => $user->id, 'message_id' => $id],
        ['read' => false]
      );

      $data = [
        'received' => $this->getMessages(),
        'sent' => $this->getSentMessages()
      ];

      return RB::success($data);
    }




    public function hideMessage($id)
    {
      $user = Auth::user();

      if (!Message::find($id)) return RB::error(404); // message not found

      DB::table('message_user')->updateOrInsert(
        ['user_id' => $user->id, 'message_id' => $id],
        ['hidden' => true]
      );

      $data = [
        'received' => $this->getMessages(),
        'sent' => $this->getSentMessages()
      ];

      return RB::success($data);
    }



    public function getMessageCount()
    {
      return RB::success($this->getCount());
    }


    public function getActiveBanners()
    {
      return RB::success(['banners' => $this->getBanners()]);
    }
}
