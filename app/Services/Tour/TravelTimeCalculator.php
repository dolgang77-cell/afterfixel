<?php

namespace App\Services\Tour;

use App\Services\GeoService;

/**
 * 지역 간 이동 시간 계산기
 */
class TravelTimeCalculator
{
    /** 지역 간 이동 시간 (분) — 심야 택시 기준 */
    private const TIMES = [
        '홍대-이태원'  => 20, '홍대-강남'  => 35, '홍대-압구정'  => 30,
        '홍대-신촌'   => 5,  '홍대-건대'  => 25, '홍대-성수'   => 25,
        '홍대-종로'   => 20,
        '이태원-강남'  => 20, '이태원-압구정' => 15, '이태원-신촌'  => 25,
        '이태원-건대'  => 30, '이태원-성수'  => 25, '이태원-종로'  => 20,
        '강남-압구정'  => 10, '강남-신촌'   => 35, '강남-건대'   => 20,
        '강남-성수'   => 15, '강남-종로'   => 30,
        '압구정-신촌'  => 30, '압구정-건대'  => 15, '압구정-성수'  => 15,
        '압구정-종로'  => 25,
        '신촌-건대'   => 30, '신촌-성수'   => 30, '신촌-종로'   => 15,
        '건대-성수'   => 10, '건대-종로'   => 25,
        '성수-종로'   => 20,
    ];

    /** 이동 시간 추정 택시비 (원/분) */
    private const TAXI_RATE_PER_MIN = 400;

    public function between(string $from, string $to): int
    {
        if ($from === $to) return 0;

        return self::TIMES["{$from}-{$to}"]
            ?? self::TIMES["{$to}-{$from}"]
            ?? $this->estimateFallback($from, $to);
    }

    public function estimateTaxiFare(string $from, string $to): int
    {
        return $this->between($from, $to) * self::TAXI_RATE_PER_MIN;
    }

    /**
     * 주어진 지역에서 가장 가까운 지역 순서 반환
     */
    public function nearestAreas(string $from, array $areas): array
    {
        $distances = [];
        foreach ($areas as $area) {
            if ($area === $from) continue;
            $distances[$area] = $this->between($from, $area);
        }
        asort($distances);
        return $distances;
    }

    private function estimateFallback(string $from, string $to): int
    {
        $fromCoords = GeoService::areaToCoords($from);
        $toCoords = GeoService::areaToCoords($to);

        if (!$fromCoords || !$toCoords) {
            return 45;
        }

        $distanceKm = GeoService::haversineDistance(
            $fromCoords['lat'],
            $fromCoords['lng'],
            $toCoords['lat'],
            $toCoords['lng']
        );

        if ($distanceKm <= 15) {
            return GeoService::estimateTravelTime($distanceKm, 'taxi');
        }

        if ($distanceKm <= 80) {
            return max(20, (int) round(($distanceKm * 1.2 / 45) * 60));
        }

        return max(60, (int) round(($distanceKm * 1.15 / 70) * 60));
    }
}
