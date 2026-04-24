<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    protected $fillable = [
        'session_id', 'user_id',
        'preferred_areas', 'preferred_genres',
        'budget_min', 'budget_max', 'foreigner_mode',
    ];

    protected $casts = [
        'preferred_areas'  => 'array',
        'preferred_genres' => 'array',
        'budget_min'       => 'integer',
        'budget_max'       => 'integer',
        'foreigner_mode'   => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function forSession(string $sessionId): self
    {
        return static::firstOrCreate(
            ['session_id' => $sessionId],
            ['preferred_areas' => [], 'preferred_genres' => []]
        );
    }

    public function hasPreferences(): bool
    {
        return !empty($this->preferred_areas)
            || !empty($this->preferred_genres)
            || $this->budget_min
            || $this->budget_max
            || $this->foreigner_mode;
    }
}
