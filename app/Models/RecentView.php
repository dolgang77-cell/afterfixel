<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class RecentView extends Model
{
    public $timestamps = false;

    protected $fillable = ['session_id', 'user_id', 'target_type', 'target_id', 'viewed_at'];

    protected $casts = ['viewed_at' => 'datetime'];

    public const TYPE_CLUB  = 'club';
    public const TYPE_PARTY = 'party';
    public const TYPE_TOUR  = 'tour';

    private const MAX_PER_TYPE = 30;

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

    /**
     * 조회 기록 저장 (중복 시 시간만 갱신, 오래된 것 자동 정리)
     */
    public static function record(string $sessionId, ?int $userId, string $type, int $targetId): void
    {
        static::updateOrCreate(
            ['session_id' => $sessionId, 'target_type' => $type, 'target_id' => $targetId],
            ['user_id' => $userId, 'viewed_at' => now()]
        );

        // 오래된 항목 정리 (타입별 최대 30개 유지)
        $keepIds = static::where('session_id', $sessionId)
            ->where('target_type', $type)
            ->orderByDesc('viewed_at')
            ->limit(self::MAX_PER_TYPE)
            ->pluck('id');

        static::where('session_id', $sessionId)
            ->where('target_type', $type)
            ->whereNotIn('id', $keepIds)
            ->delete();
    }
}
