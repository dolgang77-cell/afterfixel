<?php

namespace App\Models\Traits;

/**
 * 가격 범위 표시 트레이트
 *
 * 모델에 $priceMinField, $priceMaxField, $priceUnit 프로퍼티 정의 필요.
 * 기본값: 'price_min', 'price_max', '원'
 */
trait HasPriceRange
{
    public function getPriceTextAttribute(): string
    {
        $minField = $this->priceMinField ?? 'price_min';
        $maxField = $this->priceMaxField ?? 'price_max';
        $unit = $this->priceUnit ?? '원';

        $min = (int) $this->{$minField};
        $max = (int) $this->{$maxField};

        if ($min === 0 && $max === 0) return '무료';
        if ($min === $max) return number_format($min) . $unit;
        return number_format($min) . '~' . number_format($max) . $unit;
    }
}
