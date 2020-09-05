<?php

namespace App;

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
        return $this->belongsTo('App\User');
    }

    public function team()
    {
        return $this->belongsTo('App\Team');
    }

    public function scheduletemplate()
    {
        return $this->hasOne('App\ScheduleTemplate');
    }

    public function shifts()
    {
        return $this->hasMany('App\Shift');
    }


}
