<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
  protected $fillable = [
      'name',
      'code',
      'user_id',
      'option_shift_request_autoapproval',
      'option_shift_assignment_autoaccept',
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
}
