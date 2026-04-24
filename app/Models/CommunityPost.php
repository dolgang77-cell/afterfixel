<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunityPost extends Model
{
    protected $fillable = [
        'user_id', 'nickname', 'club_id', 'type', 'title',
        'content', 'images', 'like_count', 'report_count',
        'is_hidden', 'session_id',
    ];

    protected $casts = [
        'images'       => 'array',
        'is_hidden'    => 'boolean',
        'like_count'   => 'integer',
        'report_count' => 'integer',
    ];

    public static array $types = [
        'review'   => '후기',
        'tip'      => '팁/정보',
        'realtime' => '실시간 제보',
    ];

    public const REPORT_THRESHOLD = 5;

    // ── 관계 ──
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    // ── 접근자 ──
    public function getTypeTextAttribute(): string
    {
        return self::$types[$this->type] ?? $this->type;
    }

    // ── 스코프 ──
    public function scopeVisible($query)
    {
        return $query->where('is_hidden', false);
    }

    public function scopeOfType($query, ?string $type)
    {
        return ($type && $type !== 'all') ? $query->where('type', $type) : $query;
    }

    public function scopeRecent($query)
    {
        return $query->orderByDesc('created_at');
    }

    // ── 비즈니스 로직 ──
    public function report(): void
    {
        $this->increment('report_count');
        if ($this->report_count >= self::REPORT_THRESHOLD) {
            $this->update(['is_hidden' => true]);
        }
    }
}
