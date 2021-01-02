<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;

class Message extends Model
{
  protected $fillable = [
    'sender_id',
    'sender_type',
    'recipient_id',
    'recipient_type',
    'message_subject',
    'message_body',
    'named_route',
    'color',
    'icon',
    'show_banner',
    'expires_on'
  ];

  protected $appends = ['is_read','is_hidden'];

  

  public function sender()
  {
    return $this->morphTo();
  }

  public function recipient()
  {
    return $this->morphTo();
  }

  public function messages_global()
  {
    return $this->where('recipient_id','=',null);
  }


  public function getIsReadAttribute()
  {
    $user = Auth::user();
    return $this->belongsToMany('App\Models\User')
                ->wherePivot('user_id', $user->id)
                ->wherePivot('read', 1)
                ->count();
  }

  public function getIsHiddenAttribute()
  {
    $user = Auth::user();
    return $this->belongsToMany('App\Models\User')
                ->wherePivot('user_id', $user->id)
                ->wherePivot('hidden', 1)
                ->count();
  }
}
