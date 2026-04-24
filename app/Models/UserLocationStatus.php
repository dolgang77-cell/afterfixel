<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLocationStatus extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'last_lat',
        'last_lng',
        'last_accuracy_m',
        'last_area',
        'last_geohash',
        'venue_type',
        'venue_id',
        'is_location_enabled',
        'is_visible_nearby',
        'seen_at',
        'expires_at',
    ];

    protected $casts = [
        'last_lat' => 'float',
        'last_lng' => 'float',
        'last_accuracy_m' => 'float',
        'is_location_enabled' => 'boolean',
        'is_visible_nearby' => 'boolean',
        'seen_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_location_enabled', true)
            ->where('expires_at', '>', now());
    }
}
