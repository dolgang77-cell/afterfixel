<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    protected $fillable = [
        'session_id', 'user_id', 'enabled',
        'remind_before', 'preferred_areas', 'preferred_genres',
        'new_party_alert', 'tonight_recommendation', 'push_subscription',
    ];

    protected $casts = [
        'enabled'                => 'boolean',
        'new_party_alert'        => 'boolean',
        'tonight_recommendation' => 'boolean',
        'remind_before'          => 'array',
        'preferred_areas'        => 'array',
        'preferred_genres'       => 'array',
    ];

    public static array $remindOptions = [
        '60'   => '1시간 전',
        '180'  => '3시간 전',
        '360'  => '6시간 전',
        '720'  => '12시간 전',
        '1440' => '하루 전',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function forSession(string $sessionId): self
    {
        return static::firstOrCreate(
            ['session_id' => $sessionId],
            [
                'remind_before'   => ['1440', '180'],
                'preferred_areas' => [],
                'preferred_genres' => [],
            ]
        );
    }
}
