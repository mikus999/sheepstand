<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;

class Message extends Model
{
  protected $fillable = [
    'recipient_id',
    'recipient_type',
    'for_roles',
    'system_message',
    'message_text',
    'message_i18n_string',
    'link_text',
    'link_i18n_string',
    'named_route',
    'color',
    'type',
    'icon',
    'dismissable',
    'outlined',
    'show_banner',
    'expires_on'
  ];


  public function recipient()
  {
    return $this->morphTo();
  }

  public function messages_global()
  {
    return $this->where('recipient_id','=',null);
  }

  public function users()
  {
    $user = Auth::user();
    return $this->belongsToMany('App\Models\User')
                ->wherePivot('user_id', $user->id)
                ->wherePivot('dismissed', 1)
                ->withPivot('dismissed');
  }
  
}
