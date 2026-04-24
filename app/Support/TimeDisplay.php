<?php

namespace App\Support;

class TimeDisplay
{
    public static function clock(?string $time): ?string
    {
        $time = trim((string) $time);
        if ($time === '') {
            return null;
        }

        if (!preg_match('/^(\d{1,2}):(\d{2})/', $time, $matches)) {
            return $time;
        }

        $hour = max(0, min(23, (int) $matches[1]));
        $minute = max(0, min(59, (int) $matches[2]));
        return sprintf('%02d:%02d', $hour, $minute);
    }

    public static function range(?string $startTime, ?string $endTime): ?string
    {
        $start = self::clock($startTime);
        $end = self::clock($endTime);

        if ($start && $end) {
            return $start . '~' . $end;
        }

        return $start ?: $end;
    }
}
