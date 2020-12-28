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
      'max_participants',
      'mandatory'
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
        return $this->belongsToMany('App\Models\User')
                    ->with('marriage_mate')
                    ->withPivot('status', 'trade_user_id', 'trade_shift_id');
    }

    public function trades()
    {
        return $this->belongsToMany('App\Models\User')
                    ->with('marriage_mate')
                    ->withPivot('status', 'trade_user_id', 'trade_shift_id')
                    ->wherePivot('status',4);
    }
}
