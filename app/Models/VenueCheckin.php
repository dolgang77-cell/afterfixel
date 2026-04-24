<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VenueCheckin extends Model
{
    protected $fillable = [
        'user_id',
        'venue_type',
        'venue_id',
        'source',
        'is_active',
        'checked_in_at',
        'expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'checked_in_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($subQuery) {
                $subQuery->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }
}
