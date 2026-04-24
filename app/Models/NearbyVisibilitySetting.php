<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NearbyVisibilitySetting extends Model
{
    protected $fillable = [
        'user_id',
        'is_enabled',
        'is_visible',
        'share_scope',
        'hide_exact_venue',
        'foreign_mode',
        'preferred_languages',
        'preferred_interests',
        'preferred_intentions',
        'profile_gender',
        'profile_age_band',
        'auto_hide_after_minutes',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'is_visible' => 'boolean',
        'hide_exact_venue' => 'boolean',
        'foreign_mode' => 'boolean',
        'preferred_languages' => 'array',
        'preferred_interests' => 'array',
        'preferred_intentions' => 'array',
        'auto_hide_after_minutes' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
