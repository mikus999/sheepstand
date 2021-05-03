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
use App\Pivots\TeamUser;
use Laratrust\Traits\LaratrustUserTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable implements JWTSubject //, MustVerifyEmail
{
    use LaratrustUserTrait;
    use Notifiable;
    use HasFactory;


    protected $fillable = [
        'name', 
        'email', 
        'password', 
        'user_code', // String
        'fts_status', // Integer
        'driver', // Boolean
        'mate_id', // Integer, FK->users.id
        'max_weekly_shifts' // Integer, DEFAULT: 3
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = [
        'photo_url'
    ];

    //protected $withCount = ['available_hours'];


    public function teams()
    {
        return $this->belongsToMany(Team::class)
                    ->using(TeamUser::class)
                    ->withPivot('default_team')
                    ->withTimeStamps();
    }

    public function schedules()
    {
      return $this->hasManyThrough(Schedule::class, TeamUser::class, 'user_id', 'team_id', 'id', 'team_id');
    }

    public function shifts()
    {
        $date = Carbon::now()->sub(2, 'month');
        
        return $this->belongsToMany(Shift::class)
                    ->with('schedule')
                    ->withPivot('status', 'trade_user_id', 'trade_shift_id')
                    ->where('time_start','>=',$date)
                    ->withTimeStamps();
    }

    public function languages()
    {
        return $this->belongsToMany(Language::class);
    }

    public function messages_sent()
    {
      return $this->morphMany(Message::class, 'sender')
                  ->with('recipient','sender');
    }

    public function messages()
    {
      return $this->morphMany(Message::class, 'recipient')
                  ->with('recipient','sender');
    }

    public function messages_global()
    {
      return Message::where('recipient_id','=',null);

    }

    public function available_hours()
    {
      return $this->hasMany(UserAvailability::class)->where('available',1);
    }

    public function user_availabilities()
    {
      return $this->hasMany(UserAvailability::class);
    }

    public function user_vacations()
    {
      $oldestDate = Carbon::now()->sub(1, 'month');
      return $this->hasMany(UserVacation::class)
                  ->where('date_start','>=',$oldestDate);
    }

    public function shift_counts()
    {
      $shifts30 = $this->shifts()
                        ->where('time_start', '>=', Carbon::now()->sub(1, 'month'))
                        ->count();
      return $shifts30;
    }

    public function marriage_mate()
    {
      return $this->hasOne(User::class, 'id', 'mate_id');
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
