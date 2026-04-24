<?php

namespace App\Models\Traits;

/**
 * 조회수 트레이트 - view_count 컬럼이 있는 모델에 사용
 */
trait HasViewCount
{
    public function recordView(): void
    {
        $this->increment('view_count');
    }

    public function scopePopular($query, int $limit = 10)
    {
        return $query->orderByDesc('view_count')->limit($limit);
    }
}
