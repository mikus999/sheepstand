<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
  protected $fillable = [
    'team_id',
    'for_roles',
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
    'display_until'
  ];

  public function alerts_public()
  {
    return $this->where('team_id','=',null);
  }

  public function teams()
  {
      return $this->belongsTo('App\Models\Team')->withTimeStamps();
  }

  public function users()
  {
    return $this->belongsToMany('App\Models\User')->withPivot('dismissed');
  }
  
}
