<?php

namespace App\Models;

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
      return $this->belongsTo('App\Models\Team');
  }

  public function shifts() {
      return $this->hasMany('App\Models\Shift');
  }
}
