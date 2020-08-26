<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
  protected $fillable = [
      'team_id',
      'name',
      'color_code',
      'map',
      'default'
  ];


  public function team() {
      return $this->belongsTo('App\Team');
  }

  public function shifts() {
      return $this->hasMany('App\Shift');
  }
}
