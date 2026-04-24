<?php

namespace App\Services;

/**
 * 위치 기반 거리/이동시간 계산 서비스
 *
 * 향후 지도 API 연동 시 이 서비스만 교체하면 됨.
 * - Kakao Map Directions API
 * - Naver Map Directions API
 * - Google Maps Distance Matrix API
 */
class GeoService
{
    /** 전국 주요 나이트라이프 지역 중심 좌표 (fallback용) */
    public const AREA_CENTERS = [
        '홍대'  => ['lat' => 37.5563, 'lng' => 126.9236],
        '이태원' => ['lat' => 37.5345, 'lng' => 126.9940],
        '강남'  => ['lat' => 37.4979, 'lng' => 127.0276],
        '압구정' => ['lat' => 37.5270, 'lng' => 127.0286],
        '성수'  => ['lat' => 37.5445, 'lng' => 127.0557],
        '종로'  => ['lat' => 37.5704, 'lng' => 126.9920],
        '신촌'  => ['lat' => 37.5598, 'lng' => 126.9426],
        '건대'  => ['lat' => 37.5407, 'lng' => 127.0700],
        '부산 서면' => ['lat' => 35.1577, 'lng' => 129.0592],
        '부산 광안리' => ['lat' => 35.1532, 'lng' => 129.1186],
        '영종도' => ['lat' => 37.4473, 'lng' => 126.4527],
        '대구 동성로' => ['lat' => 35.8693, 'lng' => 128.5969],
        '대전 둔산' => ['lat' => 36.3519, 'lng' => 127.3784],
        '광주 상무지구' => ['lat' => 35.1549, 'lng' => 126.8530],
        '제주시' => ['lat' => 33.5008, 'lng' => 126.5292],
        '중문' => ['lat' => 33.2497, 'lng' => 126.4122],
    ];

    public const FEATURED_AREAS = [
        '홍대', '이태원', '강남', '부산 서면', '부산 광안리', '영종도', '대구 동성로', '제주시',
    ];

    /**
     * Haversine 공식으로 두 좌표 간 직선거리(km) 계산
     */
    public static function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2)
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
           * sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }

    /**
     * 직선거리 기반 이동시간 추정 (분)
     *
     * 보정계수: 서울 도심 기준 직선거리 × 1.4 (도로 우회 반영)
     * 속도: 심야 택시 평균 25km/h, 도보 4.5km/h
     *
     * 향후 실 API 연동 시 이 메서드만 교체
     */
    public static function estimateTravelTime(float $distanceKm, string $mode = 'taxi'): int
    {
        $roadFactor = 1.4; // 도심 도로 우회 보정
        $actualKm = $distanceKm * $roadFactor;

        $speed = match ($mode) {
            'walk'   => 4.5,
            'taxi'   => 25.0,
            'public' => 18.0,
            default  => 25.0,
        };

        $minutes = ($actualKm / $speed) * 60;

        // 최소 이동시간: 택시 5분, 도보 3분
        $min = match ($mode) {
            'walk'  => 3,
            default => 5,
        };

        return max($min, (int) round($minutes));
    }

    /**
     * 택시비 추정 (원)
     * 기본요금 4,800원 + 131m당 100원 (서울 심야 택시 기준)
     */
    public static function estimateTaxiFare(float $distanceKm): int
    {
        $baseFare = 4800;
        $actualMeters = $distanceKm * 1.4 * 1000; // 도로 보정 × m 변환
        $additionalFare = max(0, ($actualMeters - 1600)) / 131 * 100; // 기본거리 1.6km 이후

        return (int) round(($baseFare + $additionalFare) / 1000) * 1000; // 천원 단위 반올림
    }

    /**
     * 거리 표시 텍스트 생성
     */
    public static function distanceText(float $km): string
    {
        if ($km < 1) {
            return (int) round($km * 1000) . 'm';
        }
        return number_format($km, 1) . 'km';
    }

    /**
     * 이동시간 표시 텍스트
     */
    public static function travelTimeText(int $minutes): string
    {
        if ($minutes < 60) {
            return $minutes . '분';
        }
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;
        return $m > 0 ? "{$h}시간 {$m}분" : "{$h}시간";
    }

    /**
     * 좌표 → 가장 가까운 지역명 추정
     */
    public static function nearestArea(float $lat, float $lng): string
    {
        $minDist = PHP_FLOAT_MAX;
        $nearest = array_key_first(self::AREA_CENTERS) ?: '홍대';

        foreach (self::AREA_CENTERS as $area => $center) {
            $dist = self::haversineDistance($lat, $lng, $center['lat'], $center['lng']);
            if ($dist < $minDist) {
                $minDist = $dist;
                $nearest = $area;
            }
        }

        return $nearest;
    }

    /**
     * 지역명 → 중심 좌표 반환
     */
    public static function areaToCoords(string $area): ?array
    {
        return self::AREA_CENTERS[$area] ?? null;
    }

    public static function featuredAreas(): array
    {
        return self::FEATURED_AREAS;
    }

    public static function discoveryAreas(): array
    {
        return [
            ['label' => '홍대', 'emoji' => '🎸', 'color' => 'from-purple-900 to-purple-700'],
            ['label' => '이태원', 'emoji' => '🌍', 'color' => 'from-blue-900 to-blue-700'],
            ['label' => '강남', 'emoji' => '💎', 'color' => 'from-pink-900 to-pink-700'],
            ['label' => '부산 서면', 'emoji' => '🌊', 'color' => 'from-cyan-900 to-sky-700'],
            ['label' => '부산 광안리', 'emoji' => '🏖️', 'color' => 'from-teal-900 to-cyan-700'],
            ['label' => '영종도', 'emoji' => '✈️', 'color' => 'from-slate-900 to-slate-700'],
            ['label' => '대구 동성로', 'emoji' => '🎧', 'color' => 'from-red-900 to-rose-700'],
            ['label' => '대전 둔산', 'emoji' => '🌃', 'color' => 'from-indigo-900 to-indigo-700'],
            ['label' => '제주시', 'emoji' => '🍊', 'color' => 'from-amber-900 to-orange-700'],
        ];
    }

    /**
     * 반경 내 필터용: 두 좌표의 거리가 maxKm 이내인지
     */
    public static function isWithinRadius(float $lat1, float $lng1, float $lat2, float $lng2, float $maxKm): bool
    {
        return self::haversineDistance($lat1, $lng1, $lat2, $lng2) <= $maxKm;
    }
}
