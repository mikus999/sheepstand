<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserVacation extends Model
{
    protected $fillable = [
      'user_id',
      'date_start',
      'date_end',
      'note'
    ];
}
