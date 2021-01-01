<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use App\Models\Team;
use DB;
use Helper;
use Auth;


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

      return response()->json($data);

    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $data = ['message' => 'Access Denied'];
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
          'for_roles' => $request->for_roles,
          'system_message' => $request->system_message,
          'message_subject' => $request->message_subject,
          'message_body' => $request->message_body,
          'message_i18n_string' => $request->message_i18n_string,
          'named_route' => $request->named_route,
          'color' => $request->color,
          'type' => $request->type,
          'icon' => $request->icon,
          'dismissable' => $request->dismissable,
          'outlined' => $request->outlined,
          'show_banner' => $request->show_banner,
          'expires_on' => $request->expires_on
        ]);

        $data = [
          'received' => $this->getMessages(),
          'sent' => $this->getSentMessages()
        ];
      }

      return response()->json($data);
    }

    /**
     * Display the specified resource.
     *
     * @param  Integer  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Integer  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Integer  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      $data = ['message' => 'Access Denied'];
      $access_allowed = false;
      $user = Auth::user();
      $message = Message::find($id);

      if ($message->sender_type == 'App\Models\Team') {
        // If this is a team message check rights
        $team = Team::find($message->sender_id);

        if ($user->hasRole('team_admin', $team) || $user->hasRole('super_admin', null)) {
          $access_allowed = true;
        }
      } else {
        // If this is a global/system message check rights
        if ($user->hasRole('super_admin', null)) {
          $access_allowed = true;
        }
      }

      if ($access_allowed) {
        Message::destroy($id);

        $data = [
          'received' => $this->getMessages(),
          'sent' => $this->getSentMessages()
        ];
      }

      return response()->json($data);

    }


    public function markAsRead($id)
    {
      $user = Auth::user();

      DB::table('message_user')->updateOrInsert(
        ['user_id' => $user->id, 'message_id' => $id],
        ['dismissed' => true]
      );

      $data = [
        'received' => $this->getMessages(),
        'sent' => $this->getSentMessages()
      ];

      return response()->json($data);

    }

    public function getMessageCount()
    {
      $data = $this->getCount();
      
      return response()->json($data);

    }


    public function getActiveBanners()
    {
      $data = $this->getBanners();
      
      return response()->json($data);

    }
}
