<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateHelper
{
    public const KST = 'Asia/Seoul';

    /**
     * UTC datetime을 KST 문자열로 변환
     */
    public static function toKst(?Carbon $date, string $format = 'Y-m-d H:i:s'): string
    {
        if (!$date) return '-';
        return $date->copy()->setTimezone(self::KST)->format($format);
    }

    /**
     * KST 날짜 문자열을 UTC Carbon으로 변환 (필터용)
     */
    public static function kstToUtc(string $dateString, bool $endOfDay = false): Carbon
    {
        $carbon = Carbon::parse($dateString, self::KST);
        if ($endOfDay) {
            $carbon->endOfDay();
        }
        return $carbon->setTimezone('UTC');
    }

    /**
     * 짧은 KST 표시 (m-d H:i)
     */
    public static function toKstShort(?Carbon $date): string
    {
        return self::toKst($date, 'm-d H:i');
    }

    /**
     * KST 초 포함 표시
     */
    public static function toKstFull(?Carbon $date): string
    {
        return self::toKst($date, 'Y-m-d H:i:s');
    }
}
