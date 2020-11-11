<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use DB;
use Helper;
use Auth;


class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $user = Auth::user();
      $message = new Message;

      $messages_team = $user->messages()->get();
      $messages_global = $message->messages_public()->get();
      $messages = array_merge(json_decode($messages_team, true), json_decode($messages_global, true));

      $data = [
        'messages' => $messages
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
      $alert = Message::create([
        'team_id' => $request->team_id,
        'for_roles' => $request->for_roles,
        'system_message' => $request->system_message,
        'message_text' => $request->message_text,
        'message_i18n_string' => $request->message_i18n_string,
        'named_route' => $request->named_route,
        'color' => $request->color,
        'type' => $request->type,
        'icon' => $request->icon,
        'dismissable' => $request->dismissable,
        'outlined' => $request->outlined,
        'show_banner' => $request->show_banner,
        'display_until' => $request->display_until
      ]);

      return response()->json($alert);
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
        //
    }
}
