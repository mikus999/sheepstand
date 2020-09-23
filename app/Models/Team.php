<?php

namespace App\Models;

use Laratrust\Models\LaratrustTeam;

class Team extends LaratrustTeam
{
  public $guarded = [];

  protected $fillable = [
    'name',
    'display_name',
    'code',
    'description',
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
    return $this->belongsToMany('App\Models\User');
  }

  public function locations()
  {
    return $this->hasMany('App\Models\Location');
  }

  public function shifts()
  {
    return $this->hasManyThrough('App\Models\Shift', 'App\Models\Schedule');
  }
}