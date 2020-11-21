<?php
 
namespace App\Traits;

use Auth;

trait MessageTrait 
{
 
  public function getMessages()
  {
    $user = Auth::user();

    $messages_team = $user->messages()->withCount('users as unread_count')->get();
    $messages_global = $user->messages_global()->withCount('users as unread_count')->get();
    $messages = array_merge(json_decode($messages_team, true), json_decode($messages_global, true));
    usort($messages, 'self::date_compare');

    return $messages;
  }


  public function getCount()
  {
    $messages = $this->getMessages();
    $total = 0;
    $unread = 0;

    foreach ($messages as $message) {
      if ($message['unread_count'] == 0) {
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

 


  // Function used by usort to sort an array of objects by date
  public function date_compare($a, $b) 
  { 
    $t1 = strtotime($b['created_at']);
    $t2 = strtotime($a['created_at']);
    return $t1 - $t2;
  } 
}