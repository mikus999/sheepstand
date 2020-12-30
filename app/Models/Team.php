<?php

namespace App\Models;

use Laratrust\Models\LaratrustTeam;
use App\Models\Message;
use Carbon\Carbon;
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
                ->withCount([
                  'shifts as shifts_30days' => function (Builder $query) {
                    $query->where('time_start', '>=', Carbon::now()->sub(1, 'month'))
                          ->where('shift_user.status', '<>', 3);
                  },
                  'shifts as shifts_14days' => function (Builder $query) {
                    $query->where('time_start', '>=', Carbon::now()->sub(14, 'days'))
                          ->where('shift_user.status', '<>', 3);
                  },
                  'shifts as shifts_7days' => function (Builder $query) {
                    $query->where('time_start', '>=', Carbon::now()->sub(7, 'days'))
                          ->where('shift_user.status', '<>', 3);
                  },                                    
                ])
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
    return $this->morphMany(Message::class, 'recipient')->with('users','recipient','sender');
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
