<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'user_id', 'target_type', 'target_id', 'content',
        'rating', 'tags', 'like_count', 'report_count', 'is_hidden',
    ];

    protected $casts = [
        'tags'      => 'array',
        'is_hidden' => 'boolean',
    ];

    public static array $availableTags = [
        '분위기 좋음', '음악 최고', '입장 쉬움', '드레스코드 엄격',
        '가성비 좋음', '외국인 많음', '데이트 추천', '단체 추천',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function target()
    {
        return $this->morphTo('target', 'target_type', 'target_id');
    }

    /**
     * 후기에 첨부된 승인된 이미지만
     */
    public function approvedMedia()
    {
        return Media::forOwner('review', $this->id)->public()->orderBy('sort_order')->get();
    }

    public function scopeVisible($query)
    {
        return $query->where('is_hidden', false);
    }

    public function scopeForTarget($query, string $type, int $id)
    {
        return $query->where('target_type', $type)->where('target_id', $id);
    }
}
