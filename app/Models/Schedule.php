<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'team_id',
        'user_id',
        'status',
        'date_start',
        'schedule_template_id'
    ];


    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function team()
    {
        return $this->belongsTo('App\Models\Team');
    }

    public function scheduletemplate()
    {
        return $this->hasOne('App\Models\ScheduleTemplate');
    }

    public function shifts()
    {
        return $this->hasMany('App\Models\Shift')->with('location','users');
    }


}
