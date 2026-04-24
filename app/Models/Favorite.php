<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Favorite extends Model
{
    protected $fillable = ['session_id', 'user_id', 'target_type', 'target_id'];

    public const TYPE_CLUB  = 'club';
    public const TYPE_PARTY = 'party';
    public const TYPE_TOUR  = 'tour';

    public function target(): MorphTo
    {
        return $this->morphTo('target', 'target_type', 'target_id')->morphMap([
            'club'  => Club::class,
            'party' => Party::class,
            'tour'  => TourRecommendation::class,
        ]);
    }

    public function scopeForSession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('target_type', $type);
    }

    public static function isFavorited(string $sessionId, string $type, int $targetId): bool
    {
        return static::where('session_id', $sessionId)
            ->where('target_type', $type)
            ->where('target_id', $targetId)
            ->exists();
    }

    public static function toggle(string $sessionId, ?int $userId, string $type, int $targetId): bool
    {
        $existing = static::where('session_id', $sessionId)
            ->where('target_type', $type)
            ->where('target_id', $targetId)
            ->first();

        if ($existing) {
            $existing->delete();
            return false;
        }

        static::create([
            'session_id'  => $sessionId,
            'user_id'     => $userId,
            'target_type' => $type,
            'target_id'   => $targetId,
        ]);
        return true;
    }
}
