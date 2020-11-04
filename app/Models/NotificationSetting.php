<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    protected $fillable = [
        'team_id',
        'telegram_channel_id',
        'telegram_access_hash',
        'telegram_group_link',
        'setting_notify_trade_requests',
        'setting_notify_trade_filled',
        'setting_notify_schedule_open',
        'setting_notify_schedule_closed'
    ];

    public function team()
    {
        return $this->belongsTo('App\Models\Team');
    }
}
