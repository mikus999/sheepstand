<?php

namespace App\Models;

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

    public function schedule()
    {
        return $this->belongsTo('App\Models\Schedule')->with('team');
    }

    public function location()
    {
        return $this->belongsTo('App\Models\Location');
    }

    public function users()
    {
        return $this->belongsToMany('App\Models\User')->withPivot('status');
    }

    public function trades()
    {
        return $this->belongsToMany('App\Models\User')->withPivot('status')->wherePivot('status',4);
    }
}
