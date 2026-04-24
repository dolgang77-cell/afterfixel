<?php

namespace App\Services;

use App\Models\Club;
use App\Services\Tour\ClubScorer;
use App\Services\Tour\ExplanationGenerator;
use App\Services\Tour\RouteBuilder;
use App\Services\Tour\TravelTimeCalculator;

/**
 * 클럽투어 추천 서비스 (v2)
 *
 * 9차원 스코어링 → 동선 최적화 루트 빌드 → 다중 루트(최적/저비용/최단) →
 * 자연어 설명 → 경고 시스템 → 조건 완화 대안 → 예산 초과 대안
 */
class TourRecommendationService
{
    private ClubScorer $scorer;
    private RouteBuilder $builder;
    private ExplanationGenerator $explainer;
    private TravelTimeCalculator $travelCalc;

    public function __construct()
    {
        $this->travelCalc = new TravelTimeCalculator();
        $this->scorer     = new ClubScorer($this->travelCalc);
        $this->builder    = new RouteBuilder($this->travelCalc);
        $this->explainer  = new ExplanationGenerator();
    }

    /**
     * 추천 메인 엔트리포인트
     *
     * @param  array{area: string, time: string, genre?: array<int, string>|null, budget: int, foreigner_mode: ?bool} $input
     */
    public function recommend(array $input): array
    {
        $area          = $input['area'];
        $time          = $input['time'];
        $genres        = !empty($input['genre']) ? array_values(array_filter((array) $input['genre'])) : [];
        $budget        = (int) $input['budget'];
        $foreignerMode = !empty($input['foreigner_mode']);

        $clubs = Club::active()->get();

        // 사용자 선호도 (컨트롤러에서 전달)
        $userPref = $input['_user_pref'] ?? null;

        // ── 1. 전체 클럽 스코어링 ──
        $scored = $this->scorer->scoreAll($clubs, $area, $time, $genres, $budget, $foreignerMode, userPref: $userPref);

        // ── 2. 후보 부족 시 조건 완화 ──
        $relaxed        = false;
        $relaxedMessage = null;

        if (count($scored) < 2) {
            $result = $this->tryRelaxConditions($clubs, $area, $time, $genres, $budget, $foreignerMode);
            if ($result) {
                $scored         = $result['scored'];
                $relaxed        = true;
                $relaxedMessage = $result['message'];
            }
        }

        if (count($scored) < 2) {
            return [
                'routes'       => [],
                'message'      => '조건에 맞는 클럽이 충분하지 않습니다.',
                'suggestion'   => $this->buildFailSuggestion($area, $time, $genres, $budget, $foreignerMode),
                'alternatives' => [],
            ];
        }

        // ── 3. 다중 루트 생성 ──
        $routes = [];

        $optimal = $this->builder->buildOptimal($scored, $area, $time, $budget);
        if (!empty($optimal)) {
            $optimal['label'] = '추천 루트';
            $optimal['description'] = '점수와 동선을 균형있게 고려한 최적의 루트';
            $routes[] = $this->enrichRoute($optimal, $input);
        }

        // 예산 초과 시 저비용 대안 자동 생성
        if (!empty($optimal) && $optimal['total_cost'] > $budget * 0.8) {
            $budgetRoute = $this->builder->buildBudget($scored, $area, $time, $budget);
            if (!empty($budgetRoute) && $budgetRoute['total_cost'] < $optimal['total_cost']) {
                $budgetRoute['label'] = '저비용 루트';
                $budgetRoute['description'] = '예산을 아끼면서도 즐길 수 있는 루트';
                $routes[] = $this->enrichRoute($budgetRoute, $input);
            }
        }

        // 이동시간이 긴 경우 최단 루트 제안
        if (!empty($optimal) && $optimal['total_travel_minutes'] > 40) {
            $shortRoute = $this->builder->buildShortest($scored, $area, $time, $budget);
            if (!empty($shortRoute) && $shortRoute['total_travel_minutes'] < $optimal['total_travel_minutes']) {
                $shortRoute['label'] = '최단 동선';
                $shortRoute['description'] = '이동 시간을 최소화한 루트';
                $routes[] = $this->enrichRoute($shortRoute, $input);
            }
        }

        // ── 4. 결과 조립 ──
        $message    = null;
        $suggestion = null;

        if ($relaxed) {
            $message = $relaxedMessage;
        }

        if (!empty($optimal) && $optimal['total_cost'] > $budget) {
            $over = $optimal['total_cost'] - $budget;
            $message    = ($message ? $message . ' ' : '') . '입장료가 예산을 ' . number_format($over) . '원 초과합니다.';
            $suggestion = count($routes) > 1 ? '아래 저비용 루트를 확인해 보세요.' : '스탑 수를 줄이거나 무료 클럽을 포함해 보세요.';
        }

        return [
            'routes'     => $routes,
            'message'    => $message,
            'suggestion' => $suggestion,
        ];
    }

    // ── 루트에 설명/경고 요약 추가 ──

    private function enrichRoute(array $route, array $input): array
    {
        // 각 스탑에 자연어 설명 추가
        foreach ($route['stops'] as &$stop) {
            $stop['explanation'] = $this->explainer->forStop($stop);
        }
        unset($stop);

        // 루트 전체 요약
        $route['summary'] = $this->explainer->forRoute($route, $input);

        // 경고 요약
        $route['warning_summary'] = $this->explainer->warningsSummary($route['warnings']);

        return $route;
    }

    // ── 조건 완화 시도 ──

    private function tryRelaxConditions($clubs, string $area, string $time, array $genres, int $budget, bool $foreignerMode): ?array
    {
        // 전략 1: 장르 제한 제거
        if (!empty($genres)) {
            $scored = $this->scorer->scoreAll($clubs, $area, $time, [], $budget, $foreignerMode);
            if (count($scored) >= 2) {
                return [
                    'scored'  => $scored,
                    'message' => "'" . implode(', ', $genres) . "' 장르에 맞는 클럽이 부족해 장르 제한을 풀었습니다.",
                ];
            }
        }

        // 전략 2: 외국인 모드 제거
        if ($foreignerMode) {
            $scored = $this->scorer->scoreAll($clubs, $area, $time, $genres, $budget, false);
            if (count($scored) >= 2) {
                return [
                    'scored'  => $scored,
                    'message' => '외국인 모드를 완화했습니다. 일부 클럽은 외국인 입장이 제한될 수 있습니다.',
                ];
            }
        }

        // 전략 3: 인접 지역 포함 (임계값 낮추기)
        $scored = $this->scorer->scoreAll($clubs, $area, $time, [], $budget, false, threshold: 10.0);
        if (count($scored) >= 2) {
            return [
                'scored'  => $scored,
                'message' => '조건을 완화하여 인접 지역과 다양한 장르를 포함했습니다.',
            ];
        }

        return null;
    }

    // ── 실패 시 구체적 제안 ──

    private function buildFailSuggestion(string $area, string $time, array $genres, int $budget, bool $foreignerMode): string
    {
        $tips = [];

        // 시간대 확인
        $hour = (int) explode(':', $time)[0];
        if ($hour >= 3 && $hour <= 20) {
            $tips[] = '현재 시간대에는 대부분의 클럽이 영업하지 않습니다. 22시 이후를 추천합니다.';
        }

        if (!empty($genres)) {
            $tips[] = "'" . implode(', ', $genres) . "' 장르 선택을 줄이거나 해제해 보세요.";
        }

        if ($foreignerMode) {
            $tips[] = '외국인 모드를 해제하면 더 많은 클럽이 표시됩니다.';
        }

        if ($budget < 50000) {
            $tips[] = '예산을 5만원 이상으로 늘려보세요.';
        }

        $nearAreas = $this->travelCalc->nearestAreas($area, Club::$areas);
        $nearest   = array_slice(array_keys($nearAreas), 0, 2);
        if (!empty($nearest)) {
            $tips[] = implode(' 또는 ', $nearest) . ' 지역도 고려해 보세요.';
        }

        return implode(' ', $tips) ?: '다른 조건으로 다시 시도해 주세요.';
    }

    // ── 하위호환: 이전 API 에서 사용하던 getTravelTime ──

    public function getTravelTime(string $from, string $to): int
    {
        return $this->travelCalc->between($from, $to);
    }
}
