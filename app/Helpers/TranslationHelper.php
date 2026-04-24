<?php

use App\Services\AutoTranslator;

/**
 * 사용자 콘텐츠 자동번역 헬퍼
 * 현재 locale이 기본 언어(ko)가 아닐 때 자동 번역
 *
 * 사용법: {{ trans_auto($text) }}
 */
if (!function_exists('trans_auto')) {
    function trans_auto(?string $text): string
    {
        return AutoTranslator::translate($text);
    }
}
