<?php

namespace App\Services\Tour;

/**
 * 추천 이유 자연어 문장 생성기
 */
class ExplanationGenerator
{
    /**
     * 개별 스탑에 대한 추천 이유 한 문장
     */
    public function forStop(array $stop): string
    {
        $club = $stop['club'];
        $bd   = $stop['breakdown'];
        $parts = [];

        // 가장 높은 점수 차원 2~3개로 문장 구성
        $top = $this->topDimensions($bd, 3);

        foreach ($top as $dim) {
            $parts[] = match ($dim) {
                'area'       => "{$club->area} 지역에 위치해 이동이 편리하고",
                'genre'      => "{$club->genre} 장르로 취향에 딱 맞으며",
                'time'       => "이 시간대에 방문하기 좋고",
                'budget'     => "입장료가 {$club->entry_fee_text}으로 예산에 적합하며",
                'rating'     => "평점 {$club->rating_avg}점으로 만족도가 높고",
                'foreigner'  => "외국인 입장이 가능하며",
                'dresscode'  => "드레스코드가 자유로워 부담 없고",
                'proximity'  => "이전 장소에서 가까워 이동이 짧고",
                'popularity' => "많은 사람들이 찾는 인기 장소입니다",
                default      => '',
            };
        }

        $parts = array_filter($parts);
        if (empty($parts)) {
            return "{$club->name}은(는) 조건에 가장 적합한 클럽입니다.";
        }

        $sentence = implode(' ', array_slice($parts, 0, 2));
        // 마지막에 마침표 처리
        $sentence = rtrim($sentence, ',고며 ');

        return "{$club->name}: {$sentence}.";
    }

    /**
     * 루트 전체 요약 문장
     */
    public function forRoute(array $route, array $input): string
    {
        $stops    = $route['stops'];
        $count    = count($stops);
        $areas    = array_unique(array_map(fn($s) => $s['club']->area, $stops));
        $areaText = implode(' → ', array_map(fn($s) => $s['club']->area, $stops));
        $genres   = array_unique(array_map(fn($s) => $s['club']->genre, $stops));

        $parts = [];
        $parts[] = "{$input['area']}에서 시작해 {$count}곳을 돌아보는 루트입니다.";

        if (count($areas) === 1) {
            $parts[] = "{$areas[0]} 지역 내에서만 이동하므로 동선이 매우 효율적입니다.";
        } else {
            $parts[] = "{$areaText} 순서로 이동하며 총 {$route['total_travel_minutes']}분이 소요됩니다.";
        }

        if (count($genres) >= 3) {
            $parts[] = "다양한 장르를 골고루 경험할 수 있습니다.";
        } elseif (count($genres) === 1) {
            $parts[] = "{$genres[0]} 장르에 집중한 루트입니다.";
        }

        $parts[] = "입장료 총 " . number_format($route['total_cost']) . "원, 나머지 "
            . number_format($route['drink_budget']) . "원은 음료와 교통비로 활용하세요.";

        return implode(' ', $parts);
    }

    /**
     * 경고 요약 문장
     */
    public function warningsSummary(array $warnings): array
    {
        $grouped = [];
        foreach ($warnings as $w) {
            $key = $w['type'];
            $grouped[$key][] = $w;
        }

        $summaries = [];

        if (isset($grouped['closing_soon']) || isset($grouped['closing_at_arrival'])) {
            $clubs = [];
            foreach (array_merge($grouped['closing_soon'] ?? [], $grouped['closing_at_arrival'] ?? []) as $w) {
                $clubs[] = $w['club'] ?? '알 수 없음';
            }
            $summaries[] = implode(', ', array_unique($clubs)) . " — 영업 종료 임박. 일찍 방문하세요.";
        }

        if (isset($grouped['dresscode'])) {
            $count = count($grouped['dresscode']);
            $summaries[] = "{$count}곳에 드레스코드가 있습니다. 복장을 미리 확인하세요.";
        }

        if (isset($grouped['foreigner_restricted'])) {
            $summaries[] = "일부 장소는 외국인 입장이 제한됩니다.";
        }

        return $summaries;
    }

    private function topDimensions(array $breakdown, int $n): array
    {
        $dims = [];
        foreach ($breakdown as $dim => $data) {
            if ($data['score'] > 0) {
                $dims[$dim] = $data['score'];
            }
        }
        arsort($dims);
        return array_slice(array_keys($dims), 0, $n);
    }
}
