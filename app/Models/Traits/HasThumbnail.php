<?php

namespace App\Models\Traits;

use App\Support\ImageUrl;

/**
 * 썸네일 URL 트레이트
 *
 * 모델에 $defaultThumbnail 프로퍼티로 기본 이미지 지정 가능.
 */
trait HasThumbnail
{
    public function getThumbnailUrlAttribute(): string
    {
        return ImageUrl::normalize(
            $this->thumbnail,
            $this->defaultThumbnail ?? ImageUrl::default()
        );
    }

    public function getThumbnailSrcsetAttribute(): ?string
    {
        return ImageUrl::srcset($this->thumbnail);
    }
}
