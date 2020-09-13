<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
  protected $fillable = [
    'name',
    'code',
    'user_id',
    'setting_shift_request_autoapproval',
    'setting_shift_assignment_autoaccept',
    'setting_shift_trade_autoapproval',
    'default_participants_min',
    'default_participants_max',
    'default_shift_minutes'
  ];

  public function users()
  {
    return $this->belongsToMany('App\User');
  }

  public function locations()
  {
    return $this->hasMany('App\Location');
  }

  public function shifts()
  {
    return $this->hasManyThrough('App\Shift', 'App\Schedule');
  }
}
