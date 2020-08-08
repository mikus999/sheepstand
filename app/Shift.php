<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $fillable = [
      'schedule_id',
      'location_id',
      'time_start',
      'time_end',
      'min_participants',
      'max_participants'
    ];

    public function schedules()
    {
        return $this->belongsTo('App\Schedule');
    }

    public function location()
    {
        return $this->hasOne('App\Location');
    }

    public function users()
    {
        return $this->belongsToMany('App\User');
    }
}
