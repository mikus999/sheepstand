<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
  protected $fillable = [
    'team_id',
    'message_text',
    'message_i18n_string',
    'link_text',
    'link_i18n_string',
    'named_route',
    'color',
    'type',
    'icon',
    'dismissable',
    'outlined'
  ];

  public function teams()
  {
      return $this->belongsToMany('App\Models\Team')->withTimeStamps();
  }

  public function users()
  {
    return $this->belongsToMany('App\Models\User')->withPivot('dismissed');
  }
  
}
