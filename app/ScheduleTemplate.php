<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ScheduleTemplate extends Model
{
    protected $fillable = [
        'team_id',
        'name'
    ];

    public function team()
    {
        return $this->belongsTo('App\Team');
    }

    public function schedule()
    {
        return $this->belongsTo('App\schedule');
    }
}
