<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDevice extends Model
{
    protected $fillable = [
        'device_id', 'user_id', 'platform', 'device_model',
        'os', 'os_version', 'browser', 'app_version', 'build_version',
        'last_ip', 'push_token_linked', 'first_seen_at', 'last_seen_at',
    ];

    protected $casts = [
        'push_token_linked' => 'boolean',
        'first_seen_at'     => 'datetime',
        'last_seen_at'      => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 기기 정보 등록 또는 갱신
     */
    public static function upsert(string $deviceId, array $data): self
    {
        return static::updateOrCreate(
            ['device_id' => $deviceId],
            array_merge($data, ['last_seen_at' => now()]),
        );
    }
}
