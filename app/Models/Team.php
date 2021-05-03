<?php

namespace App\Models;

use Laratrust\Models\LaratrustTeam;
use App\Models\Message;
use Carbon\Carbon;
use Auth;
use Illuminate\Database\Eloquent\Builder;

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

  protected $with = ['notificationsettings'];


  public function users()
  {
    return $this->belongsToMany('App\Models\User')
                ->using('App\Pivots\TeamUser')
                ->with('marriage_mate')
                ->withPivot('default_team');
  }

  public function schedules()
  {
    return $this->hasMany('App\Models\Schedule');
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
    // Get all messages sent to the team but where user is not the sender
    $user = Auth::user();
    return $this->morphMany(Message::class, 'recipient')
                ->with('recipient','sender')
                ->where(function($query) use($user) {
                  $query->where('sender_type','App\Models\User')
                        ->where('sender_id','<>',$user->id)
                        ->orWhere('sender_type','<>','App\Models\User');
                });
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
