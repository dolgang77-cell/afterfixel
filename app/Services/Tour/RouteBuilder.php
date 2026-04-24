<?php

namespace App\Services\Tour;

/**
 * 루트 빌더 — 동선 최적화 + 다중 루트 생성
 *
 * greedy nearest-neighbor 기반으로 점수와 이동시간을 모두 고려합니다.
 */
class RouteBuilder
{
    private const MIN_STOPS       = 2;
    private const MAX_STOPS       = 5;
    private const DEFAULT_STAY    = 90;  // 분
    private const CALORIE_PER_H   = 200;
    private const CALORIE_TRAVEL  = 50;  // 이동 10분 당

    private TravelTimeCalculator $travelCalc;

    public function __construct(TravelTimeCalculator $travelCalc)
    {
        $this->travelCalc = $travelCalc;
    }

    /**
     * 최적 루트 생성 (동선 + 점수 혼합)
     */
    public function buildOptimal(array $scored, string $startArea, string $startTime, int $budget): array
    {
        return $this->build($scored, $startArea, $startTime, $budget, 'optimal');
    }

    /**
     * 저비용 루트 생성 (예산 최소화 우선)
     */
    public function buildBudget(array $scored, string $startArea, string $startTime, int $budget): array
    {
        // 비용 기준으로 재정렬
        $budgetSorted = $scored;
        usort($budgetSorted, function ($a, $b) {
            if ($a['cost'] === $b['cost']) return $b['total_score'] <=> $a['total_score'];
            return $a['cost'] <=> $b['cost'];
        });

        return $this->build($budgetSorted, $startArea, $startTime, $budget, 'budget');
    }

    /**
     * 최단 루트 생성 (이동시간 최소화)
     */
    public function buildShortest(array $scored, string $startArea, string $startTime, int $budget): array
    {
        return $this->build($scored, $startArea, $startTime, $budget, 'shortest');
    }

    // ──────────────────────────────────────
    //  핵심 빌드 로직
    // ──────────────────────────────────────

    private function build(array $candidates, string $startArea, string $startTime, int $budget, string $strategy): array
    {
        $usedIds      = [];
        $stops        = [];
        $totalCost    = 0;
        $totalTravel  = 0;
        $currentArea  = $startArea;
        $currentTime  = $startTime;
        $allWarnings  = [];

        for ($i = 0; $i < self::MAX_STOPS; $i++) {
            $best = $this->pickNext($candidates, $usedIds, $currentArea, $currentTime, $budget - $totalCost, $strategy);
            if (!$best) break;

            $travel = $i === 0 ? 0 : $this->travelCalc->between($currentArea, $best['club']->area);
            $arrivalTime = $this->addMinutes($currentTime, $travel);

            // 도착 시점 기준 영업종료 잔여시간 재확인
            $closingWarning = $this->checkClosingAtArrival($best['club'], $arrivalTime);

            $stopWarnings = $best['warnings'];
            if ($closingWarning) {
                $stopWarnings[] = $closingWarning;
            }

            $stops[] = [
                'order'          => $i + 1,
                'club'           => $best['club'],
                'arrival_time'   => $arrivalTime,
                'departure_time' => $this->addMinutes($arrivalTime, self::DEFAULT_STAY),
                'stay_minutes'   => self::DEFAULT_STAY,
                'travel_minutes' => $travel,
                'cost'           => $best['cost'],
                'taxi_fare'      => $travel > 0 ? $this->travelCalc->estimateTaxiFare($currentArea, $best['club']->area) : 0,
                'score'          => $best['total_score'],
                'breakdown'      => $best['breakdown'],
                'warnings'       => $stopWarnings,
                'reasons'        => $this->extractTopReasons($best['breakdown']),
            ];

            foreach ($stopWarnings as $w) {
                $allWarnings[] = array_merge($w, ['stop' => $i + 1, 'club' => $best['club']->name]);
            }

            $usedIds[]    = $best['club']->id;
            $totalCost   += $best['cost'];
            $totalTravel += $travel;
            $currentArea  = $best['club']->area;
            $currentTime  = $this->addMinutes($arrivalTime, self::DEFAULT_STAY);

            // 예산 초과 & 최소 스탑 충족 시 중단
            if ($totalCost + 10000 > $budget && count($stops) >= self::MIN_STOPS) {
                break;
            }
        }

        if (empty($stops)) {
            return [];
        }

        $totalStayH   = count($stops) * (self::DEFAULT_STAY / 60);
        $calorieScore = round($totalStayH * self::CALORIE_PER_H + ($totalTravel / 10) * self::CALORIE_TRAVEL);
        $avgScore     = array_sum(array_column($stops, 'score')) / count($stops);
        $probability  = min(95, round($avgScore * 1.1));
        $totalTaxi    = array_sum(array_column($stops, 'taxi_fare'));

        return [
            'strategy'             => $strategy,
            'stops'                => $stops,
            'total_stops'          => count($stops),
            'total_cost'           => $totalCost,
            'total_travel_minutes' => $totalTravel,
            'total_taxi_fare'      => $totalTaxi,
            'drink_budget'         => max(0, $budget - $totalCost - $totalTaxi),
            'probability_score'    => $probability,
            'calorie_score'        => $calorieScore,
            'avg_score'            => round($avgScore, 1),
            'warnings'             => $allWarnings,
        ];
    }

    /**
     * 다음 스탑 선택 — 전략별 분기
     */
    private function pickNext(array $candidates, array $usedIds, string $currentArea, string $currentTime, int $remainBudget, string $strategy): ?array
    {
        $viable = array_filter($candidates, function ($c) use ($usedIds, $currentTime) {
            return !in_array($c['club']->id, $usedIds)
                && $c['total_score'] > 0
                && $c['club']->isOpenAt($currentTime); // 입력 시간 기준 영업 중
        });

        if (empty($viable)) return null;

        if ($strategy === 'shortest') {
            // 가장 가까운 곳 (동일 거리면 높은 점수)
            usort($viable, function ($a, $b) use ($currentArea) {
                $ta = $this->travelCalc->between($currentArea, $a['club']->area);
                $tb = $this->travelCalc->between($currentArea, $b['club']->area);
                if ($ta === $tb) return $b['total_score'] <=> $a['total_score'];
                return $ta <=> $tb;
            });
            return $viable[0];
        }

        if ($strategy === 'budget') {
            // 비용 최소 (동일 비용이면 가까운 곳)
            usort($viable, function ($a, $b) use ($currentArea) {
                if ($a['cost'] === $b['cost']) {
                    $ta = $this->travelCalc->between($currentArea, $a['club']->area);
                    $tb = $this->travelCalc->between($currentArea, $b['club']->area);
                    return $ta <=> $tb;
                }
                return $a['cost'] <=> $b['cost'];
            });
            return $viable[0];
        }

        // optimal: 점수 × 0.7 + 근접도 × 0.3 혼합
        usort($viable, function ($a, $b) use ($currentArea) {
            $scoreA = $a['total_score'] * 0.7 + (100 - min(100, $this->travelCalc->between($currentArea, $a['club']->area) * 3)) * 0.3;
            $scoreB = $b['total_score'] * 0.7 + (100 - min(100, $this->travelCalc->between($currentArea, $b['club']->area) * 3)) * 0.3;
            return $scoreB <=> $scoreA;
        });

        return $viable[0];
    }

    /**
     * breakdown에서 상위 이유 3개 추출
     */
    private function extractTopReasons(array $breakdown): array
    {
        $items = [];
        foreach ($breakdown as $dim => $data) {
            if (!empty($data['reason']) && $data['score'] > 0) {
                $items[] = ['dim' => $dim, 'score' => $data['score'], 'reason' => $data['reason']];
            }
        }
        usort($items, fn($a, $b) => $b['score'] <=> $a['score']);

        return array_map(fn($i) => $i['reason'], array_slice($items, 0, 4));
    }

    private function checkClosingAtArrival($club, string $arrivalTime): ?array
    {
        if (!$club->close_time) return null;

        $arrive = $this->toMinutes($arrivalTime);
        $close  = $this->toMinutes($club->close_time);
        if ($close <= $arrive) $close += 1440;
        $remain = $close - $arrive;

        if ($remain <= 60) {
            return [
                'type'     => 'closing_at_arrival',
                'message'  => "도착 시점 기준 마감 {$remain}분 전입니다",
                'severity' => 'high',
            ];
        }
        if ($remain <= 90) {
            return [
                'type'     => 'closing_at_arrival',
                'message'  => "도착 후 약 {$remain}분 뒤 마감",
                'severity' => 'medium',
            ];
        }

        return null;
    }

    private function addMinutes(string $time, int $minutes): string
    {
        [$h, $m] = array_map('intval', explode(':', $time));
        $total = $h * 60 + $m + $minutes;
        return sprintf('%02d:%02d', intdiv($total, 60) % 24, $total % 60);
    }

    private function toMinutes(string $time): int
    {
        [$h, $m] = array_map('intval', explode(':', $time));
        return $h * 60 + $m;
    }
}
