<?php

namespace App\Services\Tour;

use App\Models\Club;

/**
 * 클럽 스코어링 엔진 — 9개 차원 독립 평가
 *
 * 각 차원은 0~100 점 범위의 정규화된 점수를 반환하며,
 * 가중치를 곱해 최종 점수를 산출합니다.
 */
class ClubScorer
{
    // ── 가중치 (합계 = 1.0) ──
    private const WEIGHTS = [
        'area'       => 0.20,  // 지역 일치도
        'time'       => 0.15,  // 시간대 적합성
        'genre'      => 0.15,  // 장르 일치도
        'budget'     => 0.12,  // 예산 적합성
        'rating'     => 0.10,  // 평점 + 인기도
        'foreigner'  => 0.10,  // 외국인 모드
        'dresscode'  => 0.08,  // 드레스코드 위험도
        'proximity'  => 0.05,  // 동선(현재 위치 기준)
        'popularity' => 0.05,  // 조회수/리뷰수 기반 인기
    ];

    private TravelTimeCalculator $travelCalc;

    public function __construct(TravelTimeCalculator $travelCalc)
    {
        $this->travelCalc = $travelCalc;
    }

    /**
     * 클럽 하나를 9개 차원으로 평가
     *
     * @return array{club: Club, total_score: float, breakdown: array, warnings: array, cost: int, explanation: string}
     */
    public function score(
        Club   $club,
        string $area,
        string $time,
        array $genres,
        int    $budget,
        bool   $foreignerMode,
        ?string $currentArea = null,
    ): array {
        $breakdown = [];
        $warnings  = [];

        // 1. 지역 일치도
        $breakdown['area'] = $this->scoreArea($club, $area);

        // 2. 시간대 적합성 (도착 예상 시간 기준)
        $breakdown['time'] = $this->scoreTime($club, $time, $warnings);

        // 3. 장르 일치도
        $breakdown['genre'] = $this->scoreGenre($club, $genres);

        // 4. 예산 적합성
        $breakdown['budget'] = $this->scoreBudget($club, $budget);

        // 5. 평점 품질
        $breakdown['rating'] = $this->scoreRating($club);

        // 6. 외국인 모드
        $breakdown['foreigner'] = $this->scoreForeigner($club, $foreignerMode, $warnings);

        // 7. 드레스코드 위험도
        $breakdown['dresscode'] = $this->scoreDresscode($club, $warnings);

        // 8. 동선 근접도 (현재 위치 → 이 클럽)
        $breakdown['proximity'] = $this->scoreProximity($club, $currentArea ?? $area);

        // 9. 인기도 (조회수 + 리뷰수)
        $breakdown['popularity'] = $this->scorePopularity($club);

        // 가중 합산
        $totalScore = 0;
        foreach (self::WEIGHTS as $dim => $weight) {
            $totalScore += ($breakdown[$dim]['score'] ?? 0) * $weight;
        }

        $avgCost = (int) round(($club->entry_fee_min + $club->entry_fee_max) / 2);

        return [
            'club'        => $club,
            'total_score' => round($totalScore, 1),
            'breakdown'   => $breakdown,
            'warnings'    => $warnings,
            'cost'        => $avgCost,
        ];
    }

    /**
     * 여러 클럽을 한번에 스코어링하고 임계값 이상만 반환
     */
    public function scoreAll(
        $clubs,
        string $area,
        string $time,
        array $genres,
        int $budget,
        bool $foreignerMode,
        float $threshold = 20.0,
        ?array $userPref = null,
    ): array {
        $results = [];
        foreach ($clubs as $club) {
            $scored = $this->score($club, $area, $time, $genres, $budget, $foreignerMode);

            // 사용자 선호도 보너스 (최대 +10점)
            if ($userPref) {
                $bonus = 0;
                if (!empty($userPref['preferred_genres']) && in_array($club->genre, $userPref['preferred_genres'])) {
                    $bonus += 5;
                }
                if (!empty($userPref['preferred_areas']) && in_array($club->area, $userPref['preferred_areas'])) {
                    $bonus += 3;
                }
                if (!empty($userPref['favorite_club_ids']) && in_array($club->id, $userPref['favorite_club_ids'])) {
                    $bonus += 2;
                }
                if ($bonus > 0) {
                    $scored['total_score'] = min(100, $scored['total_score'] + $bonus);
                    $scored['user_pref_applied'] = true;
                }
            }

            if ($scored['total_score'] >= $threshold) {
                $results[] = $scored;
            }
        }

        usort($results, fn($a, $b) => $b['total_score'] <=> $a['total_score']);

        return $results;
    }

    // ══════════════════════════════════════════
    //  개별 차원 스코어링 (각각 0~100)
    // ══════════════════════════════════════════

    private function scoreArea(Club $club, string $area): array
    {
        if ($club->area === $area) {
            return ['score' => 100, 'reason' => "{$area} 지역 내 위치"];
        }

        $travel = $this->travelCalc->between($area, $club->area);

        if ($travel <= 10) {
            return ['score' => 80, 'reason' => "인접 지역 ({$travel}분 거리)"];
        }
        if ($travel <= 20) {
            return ['score' => 55, 'reason' => "이동 가능 거리 ({$travel}분)"];
        }
        if ($travel <= 30) {
            return ['score' => 30, 'reason' => "다소 먼 거리 ({$travel}분)"];
        }

        return ['score' => 10, 'reason' => "먼 지역 ({$travel}분 이상)"];
    }

    private function scoreTime(Club $club, string $time, array &$warnings): array
    {
        if (!$club->open_time || !$club->close_time) {
            return ['score' => 70, 'reason' => '영업시간 미확인'];
        }

        if (!$club->isOpenAt($time)) {
            return ['score' => 0, 'reason' => "영업시간 외 ({$club->open_time}~{$club->close_time})"];
        }

        // 영업 종료까지 남은 시간 계산
        $remainMin = $this->minutesUntilClose($time, $club->close_time);

        if ($remainMin <= 60) {
            $warnings[] = [
                'type'    => 'closing_soon',
                'message' => "영업 종료 {$remainMin}분 전 — 서두르세요!",
                'severity' => 'high',
            ];
            return ['score' => 30, 'reason' => "곧 마감 ({$remainMin}분 남음)"];
        }

        if ($remainMin <= 120) {
            $warnings[] = [
                'type'    => 'closing_soon',
                'message' => "영업 종료까지 약 {$remainMin}분 남았습니다",
                'severity' => 'medium',
            ];
            return ['score' => 60, 'reason' => "영업 중 ({$remainMin}분 남음)"];
        }

        // 개장 직후(골든타임)이면 보너스
        $sinceOpen = $this->minutesSinceOpen($time, $club->open_time);
        if ($sinceOpen <= 60) {
            return ['score' => 85, 'reason' => "방금 개장 — 여유있는 입장"];
        }

        return ['score' => 90, 'reason' => "영업 중 ({$club->open_time}~{$club->close_time})"];
    }

    private function scoreGenre(Club $club, array $genres): array
    {
        if (empty($genres)) {
            return ['score' => 60, 'reason' => '장르 무관'];
        }

        $bestMatch = ['score' => 15, 'reason' => "다른 장르 ({$club->genre})"];

        foreach ($genres as $genre) {
            $scored = $this->scoreGenreOption($club, $genre);
            if ($scored['score'] > $bestMatch['score']) {
                $bestMatch = $scored;
            }
        }

        return $bestMatch;
    }

    private function scoreGenreOption(Club $club, string $genre): array
    {
        if ($club->genre === $genre) {
            return ['score' => 100, 'reason' => "메인 장르 일치 ({$club->genre})"];
        }

        if ($club->subgenre === $genre) {
            return ['score' => 80, 'reason' => "서브 장르 일치 ({$club->subgenre})"];
        }

        if (is_array($club->tags) && in_array($genre, $club->tags)) {
            return ['score' => 55, 'reason' => "관련 장르 포함 ({$genre})"];
        }

        // 유사 장르 매핑
        $similar = [
            '힙합'   => ['R&B', '팝', '케이팝'],
            'R&B'    => ['힙합', '팝', '재즈'],
            'EDM'    => ['하우스', '테크노', '팝'],
            '테크노'  => ['하우스', 'EDM'],
            '하우스'  => ['테크노', 'EDM', '재즈'],
            '팝'     => ['케이팝', 'EDM', 'R&B'],
            '케이팝'  => ['팝', '힙합'],
            '재즈'   => ['R&B', '하우스'],
            '록'     => ['팝'],
        ];

        if (isset($similar[$genre]) && in_array($club->genre, $similar[$genre])) {
            return ['score' => 40, 'reason' => "유사 장르 ({$club->genre})"];
        }

        return ['score' => 15, 'reason' => "다른 장르 ({$club->genre})"];
    }

    private function scoreBudget(Club $club, int $budget): array
    {
        $avgFee = ($club->entry_fee_min + $club->entry_fee_max) / 2;

        if ($avgFee == 0) {
            return ['score' => 100, 'reason' => '무료 입장'];
        }

        $ratio = $avgFee / $budget;

        if ($ratio <= 0.15) {
            return ['score' => 95, 'reason' => "매우 저렴 ({$club->entry_fee_text})"];
        }
        if ($ratio <= 0.25) {
            return ['score' => 80, 'reason' => "예산 여유 ({$club->entry_fee_text})"];
        }
        if ($ratio <= 0.40) {
            return ['score' => 60, 'reason' => "적정 가격 ({$club->entry_fee_text})"];
        }
        if ($ratio <= 0.60) {
            return ['score' => 35, 'reason' => "다소 비쌈 ({$club->entry_fee_text})"];
        }

        return ['score' => 10, 'reason' => "예산 대비 고가 ({$club->entry_fee_text})"];
    }

    private function scoreRating(Club $club): array
    {
        $avg   = $club->rating_avg;
        $count = $club->rating_count;

        // 베이지안 평균: 리뷰 수가 적으면 전체 평균(4.0)으로 수렴
        $prior    = 4.0;
        $priorN   = 10;
        $bayesian = ($avg * $count + $prior * $priorN) / ($count + $priorN);

        $score = min(100, max(0, ($bayesian - 3.0) * 50)); // 3.0→0, 5.0→100

        $label = $avg >= 4.5 ? '최고 평점' : ($avg >= 4.0 ? '높은 평점' : '보통 평점');

        return ['score' => round($score), 'reason' => "{$label} {$avg}점 ({$count}건)"];
    }

    private function scoreForeigner(Club $club, bool $foreignerMode, array &$warnings): array
    {
        if (!$foreignerMode) {
            return ['score' => 60, 'reason' => ''];
        }

        if ($club->foreigner_allowed) {
            return ['score' => 100, 'reason' => '외국인 입장 가능'];
        }

        $warnings[] = [
            'type'     => 'foreigner_restricted',
            'message'  => '외국인 입장이 제한될 수 있습니다',
            'severity' => 'high',
        ];
        return ['score' => 0, 'reason' => '외국인 입장 불가'];
    }

    private function scoreDresscode(Club $club, array &$warnings): array
    {
        $dc = $club->dress_code;

        if (!$dc || $dc === '자유' || $dc === '캐주얼') {
            return ['score' => 90, 'reason' => '드레스코드 자유'];
        }

        if (in_array($dc, ['스마트 캐주얼'])) {
            $warnings[] = [
                'type'     => 'dresscode',
                'message'  => "드레스코드 주의: {$dc}" . ($club->dress_code_detail ? " — {$club->dress_code_detail}" : ''),
                'severity' => 'medium',
            ];
            return ['score' => 60, 'reason' => "드레스코드 주의 ({$dc})"];
        }

        // 세미 포멀, 블랙, 엄격한 코드
        $warnings[] = [
            'type'     => 'dresscode',
            'message'  => "엄격한 드레스코드: {$dc}" . ($club->dress_code_detail ? " — {$club->dress_code_detail}" : ''),
            'severity' => 'high',
        ];
        return ['score' => 30, 'reason' => "엄격한 드레스코드 ({$dc})"];
    }

    private function scoreProximity(Club $club, string $fromArea): array
    {
        $travel = $this->travelCalc->between($fromArea, $club->area);

        if ($travel === 0) return ['score' => 100, 'reason' => '같은 지역'];
        if ($travel <= 10) return ['score' => 85, 'reason' => "가까운 거리 ({$travel}분)"];
        if ($travel <= 20) return ['score' => 60, 'reason' => "적당한 거리 ({$travel}분)"];
        if ($travel <= 30) return ['score' => 35, 'reason' => "먼 거리 ({$travel}분)"];

        return ['score' => 15, 'reason' => "매우 먼 거리 ({$travel}분)"];
    }

    private function scorePopularity(Club $club): array
    {
        $viewScore  = min(50, ($club->view_count ?? 0) / 10);
        $countScore = min(50, ($club->rating_count ?? 0) / 6);
        $total      = round($viewScore + $countScore);

        if ($total >= 70) return ['score' => $total, 'reason' => '인기 핫플'];
        if ($total >= 40) return ['score' => $total, 'reason' => '많은 사람들이 방문'];

        return ['score' => max(20, $total), 'reason' => '새로운 곳 발견'];
    }

    // ── 시간 유틸 ──

    private function minutesUntilClose(string $now, string $closeTime): int
    {
        $n = $this->toMinutes($now);
        $c = $this->toMinutes($closeTime);
        if ($c <= $n) $c += 1440; // 자정 넘김
        return $c - $n;
    }

    private function minutesSinceOpen(string $now, string $openTime): int
    {
        $n = $this->toMinutes($now);
        $o = $this->toMinutes($openTime);
        if ($n < $o) $n += 1440;
        return $n - $o;
    }

    private function toMinutes(string $time): int
    {
        [$h, $m] = array_map('intval', explode(':', $time));
        return $h * 60 + $m;
    }
}
