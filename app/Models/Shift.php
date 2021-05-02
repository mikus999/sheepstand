<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;
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
    
      // TODO Show number of available users for each shift time (check weekly availability)


    protected $with = ['schedule','location'];

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
        $user = Auth::user();

        return $this->belongsToMany('App\Models\User')
                    ->with('marriage_mate')
                    ->withPivot('status', 'trade_user_id', 'trade_shift_id')
                    ->where(function($query) use($user) {
                      $query->where('shift_user.status', 5)
                            ->where('shift_user.trade_user_id', $user->id)
                            ->orWhere('shift_user.status', 4);
                      });
    }
}
