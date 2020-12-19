<?php

namespace App\Models;

use Laratrust\Models\LaratrustTeam;
use App\Models\Message;

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
    return $this->belongsToMany('App\Models\User')->withPivot('default_team');
  }

  public function schedules()
  {
    return $this->hasMany('App\Models\Schedules');
  }

  public function locations()
  {
    return $this->hasMany('App\Models\Location');
  }

  public function shifts()
  {
    return $this->hasManyThrough('App\Models\Shift', 'App\Models\Schedule');
  }

  public function notificationsettings()
  {
      return $this->hasOne('App\Models\NotificationSetting');
  }

  public function messages()
  {
    return $this->hasMany('App\Models\Message');
  }

  public function getTeamRoleAttribute()
  {
    return $this->name;
  }

  public function user_availability()
  {
    return $this->belongsToMany('App\Models\User')->with(['user_availabilities', 'user_vacations']);
  }

  public function user_vacation()
  {
    return $this->hasManyThrough('App\Models\UserVacation', 'App\Models\User');
  } 
}
