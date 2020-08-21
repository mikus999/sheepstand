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
        'availableday_mon',
        'availableday_tues',
        'availableday_wed',
        'availableday_thur',
        'availableday_fri',
        'availableday_sat',
        'availableday_sun',
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
