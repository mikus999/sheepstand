<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
  protected $fillable = [
      'name',
      'code',
      'user_id',
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
