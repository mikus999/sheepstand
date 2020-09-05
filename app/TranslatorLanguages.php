<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TranslatorLanguages extends Model
{
    protected $fillable = [
        'user_id',
        'language'
    ];

}
