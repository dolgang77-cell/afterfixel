<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceToken extends Model
{
    protected $fillable = [
        'user_id', 'platform', 'token', 'is_active', 'last_seen_at',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function register(int $userId, string $platform, string $token): self
    {
        return static::updateOrCreate(
            ['user_id' => $userId, 'token' => $token],
            ['platform' => $platform, 'is_active' => true, 'last_seen_at' => now()],
        );
    }
}
