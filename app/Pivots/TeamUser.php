<?php
namespace App\Pivots;
    
use Illuminate\Database\Eloquent\Relations\Pivot;

class TeamUser extends Pivot {
    
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
    
    public function team()
    {
        return $this->belongsTo('App\Models\Team');
    }
    
    public function schedules()
    {
        return $this->hasManyThrough('App\Models\Schedule', 'App\Models\Team');
    }
   
}