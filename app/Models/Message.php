<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;

class Message extends Model
{
  protected $fillable = [
    'team_id',
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

  public function messages_global()
  {
    return $this->where('team_id','=',null);
  }

  public function team()
  {
      return $this->belongsTo('App\Models\Team');
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
