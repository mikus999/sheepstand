<?php

namespace App\Models;

use App\Notifications\ResetPassword;
use App\Notifications\VerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Model;
use App\Models\Team;
use Laratrust\Traits\LaratrustUserTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable implements JWTSubject //, MustVerifyEmail
{
    use LaratrustUserTrait;
    use Notifiable;
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'user_code', 'fts_status'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'photo_url'
    ];


    //protected $with = ['user_availabilities', 'user_vacations'];
    

    public function teams()
    {
        return $this->belongsToMany('App\Models\Team')
                    ->using('App\Pivots\TeamUser')
                    ->withPivot('default_team')
                    ->with('notificationsettings')
                    ->withTimeStamps();
    }

    public function schedules()
    {
      return $this->hasManyThrough(
        'App\Models\Schedule',          // The model to access to
        'App\Pivots\TeamUser', // The intermediate table that connects the User with the Podcast.
        'user_id',                 // The column of the intermediate table that connects to this model by its ID.
        'team_id',              // The column of the intermediate table that connects the Podcast by its ID.
        'id',                      // The column that connects this model with the intermediate model table.
        'team_id'               // The column of the Audio Files table that ties it to the Podcast.
      );
    }

    public function shifts()
    {
        $date = Carbon::now()->sub(2, 'month');
        
        return $this->belongsToMany('App\Models\Shift')
                    ->with('schedule')
                    ->withPivot('status', 'trade_user_id', 'trade_shift_id')
                    ->where('time_start','>=',$date)
                    ->withTimeStamps();
    }

    public function languages()
    {
        return $this->belongsToMany('App\Models\Language');
    }

    public function messages()
    {
      return $this->hasManyThrough('App\Models\Message', 'App\Models\Team')
                  ->with('users','team');
    }

    public function messages_global()
    {
      return Message::where('team_id','=',null)->with('users','team');
    }

    public function user_availabilities()
    {
      return $this->hasMany('App\Models\UserAvailability');
    }

    public function user_vacations()
    {
      $oldestDate = Carbon::now()->sub(1, 'month');
      return $this->hasMany('App\Models\UserVacation')
                  ->where('date_start','>=',$oldestDate);
    }

    public function shift_counts()
    {
      $shifts30 = $this->shifts()
                        ->where('time_start', '>=', Carbon::now()->sub(1, 'month'))
                        ->count();
      return $shifts30;
    }



    /**
     * Get the profile photo URL attribute.
     *
     * @return string
     */
    public function getPhotoUrlAttribute()
    {
        return 'https://www.gravatar.com/avatar/'.md5(strtolower($this->email)).'.jpg?s=200&d=mm';
    }

    /**
     * Get the oauth providers.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function oauthProviders()
    {
        return $this->hasMany(OAuthProvider::class);
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail);
    }

    /**
     * @return int
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
