<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
  protected $fillable = [
      'team_id',
      'name',
      'color_code',
      'map'
  ];


  public function team() {
      return $this->belongsTo('App\Team');
  }

  public function shifts() {
      return $this->belongsToMany('App\Shift');
  }
}
