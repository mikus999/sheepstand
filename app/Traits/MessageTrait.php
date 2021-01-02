<?php
 
namespace App\Traits;

use Auth;
use Carbon\Carbon;

trait MessageTrait 
{
 
  public function getMessages()
  {
    $user = Auth::user();
    $teams = $user->teams()->get();


    // GET ALL MESSAGES WITH USER AS RECIPIENT
    $messages = collect($user->messages()->get());


    // GET ALL GLOBAL MESSAGES (WITH RECIPIENT == NULL)
    $messages_global = collect($user->messages_global()->get());
    $messages = $messages->merge($messages_global);


    // GET MESSAGES WITH TEAM AS RECIPIENT (FOR ALL USER'S TEAMS)
    foreach ($teams as $team) {
      $temp = collect($team->messages()->get());
      $messages = $messages->merge($temp);
    }


    // FINAL SORTING AND FILTERING
    $messages = $messages
                  ->sortByDesc('created_at')
                  ->filter(function($model) { return $model->is_hidden == false; })
                  ->values();

    return $messages;
  }


  public function getSentMessages()
  {
    $user = Auth::user();

    $messages = collect($user->messages_sent()->get());

    // FINAL SORTING AND FILTERING
    $messages = $messages
    ->sortByDesc('created_at')
    ->filter(function($model) { return $model->is_hidden == false; })
    ->values();

    return $messages;
  }


  public function getBanners()
  {
    $user = Auth::user();
    $messages = collect($this->getMessages());

    $banners = $messages->filter(function ($message, $key) {
                            return $message['is_read'] == 0 && $message['show_banner'] == true &&
                            ($message['expires_on'] == null || $message['expires_on'] >= Carbon::now());
                          })->sortByDesc('created_at')->values();
    
    return $banners;
  }


  public function getCount()
  {
    $user = Auth::user();
    $messages = collect($this->getMessages());
    $total = 0;
    $unread = 0;

    foreach ($messages as $message) {
      if (($message['is_read'] == 0) && 
          ($message['expires_on'] == null || $message['expires_on'] >= Carbon::now())) 
      {
        $unread += 1;
      }
      $total += 1;
    };

    $data = [
      'unread' => $unread,
      'total' => $total
    ];

    return $data;
  }

 
}