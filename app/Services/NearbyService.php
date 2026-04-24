<?php

namespace App\Services;

use App\Models\Club;
use App\Models\Party;
use App\Models\UserPreference;

class NearbyService
{
    public function __construct(private readonly AvailabilitySignalService $availabilitySignals)
    {
    }

    /**
     * Near Me 페이지용 섹션 빌드
     */
    public function buildNearMeSections(
        float $lat, float $lng, string $sessionId,
        ?UserPreference $pref, ?string $genre = null,
        ?int $budget = null, float $radius = 5.0
    ): array {
        $nearbyClubs = $this->findNearbyClubs($lat, $lng, $radius);
        $nearbyParties = $this->findNearbyParties($lat, $lng, $radius);

        if ($genre) {
            $nearbyClubs = $nearbyClubs->filter(fn($c) => $c->genre === $genre);
            $nearbyParties = $nearbyParties->filter(fn($p) => $p->genre === $genre);
        }
        if ($budget) {
            $nearbyClubs = $nearbyClubs->filter(fn($c) => !$c->entry_fee_min || $c->entry_fee_min <= $budget);
        }

        $nowTime = now()->setTimezone('Asia/Seoul')->format('H:i');
        $items = collect();

        foreach ($nearbyClubs as $club) {
            $dist = GeoService::haversineDistance($lat, $lng, $club->lat ?? 0, $club->lng ?? 0);
            $labels = [];
            $reasons = [];

            if ($club->isOpenAt($nowTime)) {
                $labels[] = ['text' => '영업중', 'color' => 'green'];
                $reasons[] = '현재 영업중';
            }
            if ($club->foreigner_allowed) {
                $labels[] = ['text' => '외국인 가능', 'color' => 'blue'];
            }
            if ($dist <= 1.0) {
                $reasons[] = '도보 가능 거리';
            }

            $items->push([
                'type'          => 'club',
                'entity'        => $club,
                'distance'      => $dist,
                'distance_text' => $dist < 1 ? round($dist * 1000) . 'm' : round($dist, 1) . 'km',
                'travel_text'   => $this->estimateTravelTime($dist),
                'travel_min'    => (int) ceil($dist / 30 * 60),
                'cost_text'     => $club->price_text,
                'time_display'  => $club->operating_hours_text,
                'labels'        => $labels,
                'reasons'       => $reasons,
                'link'          => route('clubs.show', $club),
            ]);
        }

        foreach ($nearbyParties as $party) {
            $clubLat = $party->club?->lat ?? 0;
            $clubLng = $party->club?->lng ?? 0;
            $dist = GeoService::haversineDistance($lat, $lng, $clubLat, $clubLng);
            $labels = [[
                'text' => $party->event_card_label,
                'color' => $party->is_verified_event ? 'green' : ($party->is_operating_card ? 'cyan' : 'blue'),
            ]];
            $reasons = [$party->event_card_notice];

            if ($party->event_date->isToday()) {
                $labels[] = ['text' => 'TONIGHT', 'color' => 'green'];
                $reasons[] = '오늘 파티';
            }

            $items->push([
                'type'          => 'party',
                'entity'        => $party,
                'distance'      => $dist,
                'distance_text' => $dist < 1 ? round($dist * 1000) . 'm' : round($dist, 1) . 'km',
                'travel_text'   => $this->estimateTravelTime($dist),
                'travel_min'    => (int) ceil($dist / 30 * 60),
                'cost_text'     => $party->price_text,
                'time_display'  => $party->time_range_text,
                'labels'        => $labels,
                'reasons'       => $reasons,
                'link'          => route('parties.show', $party),
            ]);
        }

        $signals = $this->availabilitySignals->forTargets(
            $items->mapWithKeys(function (array $item) {
                return [
                    $item['type'] . ':' . $item['entity']->id => [
                        'type' => $item['type'],
                        'id' => $item['entity']->id,
                        'start_time' => $item['type'] === 'party' ? $item['entity']->start_time : null,
                        'open_time' => $item['type'] === 'club' ? $item['entity']->open_time : null,
                        'close_time' => $item['type'] === 'club' ? $item['entity']->close_time : null,
                    ],
                ];
            })->all()
        );

        $items = $items
            ->map(function (array $item) use ($signals) {
                $signal = $signals[$item['type'] . ':' . $item['entity']->id] ?? null;

                if (!$signal) {
                    return $item;
                }

                $item['availability_signal'] = $signal['availability_signal'] ?? null;
                $item['crowd_signal'] = $signal['crowd_signal'] ?? null;
                $item['best_visit_window'] = $signal['best_visit_window'] ?? null;
                $item['availability_summary'] = $signal['availability_summary'] ?? null;

                $labels = collect($item['labels'] ?? []);
                foreach (['availability_signal', 'crowd_signal'] as $field) {
                    $badge = $signal[$field] ?? null;
                    if (!$badge) {
                        continue;
                    }

                    if (!$labels->contains(fn ($label) => ($label['text'] ?? null) === $badge['label'])) {
                        $labels->push($badge);
                    }
                }

                $item['labels'] = $labels->take(4)->values()->all();

                if (!empty($signal['best_visit_window'])) {
                    $item['reasons'][] = $signal['best_visit_window'];
                }

                return $item;
            })
            ->sortBy('distance')
            ->values();

        return [
            'items'       => $items,
            'total'       => $items->count(),
            'nearestArea' => GeoService::nearestArea($lat, $lng),
        ];
    }

    /**
     * 상태 요약
     */
    public function getNearbyStatusSummary(float $lat, float $lng, float $radius = 3.0): array
    {
        $nowTime = now()->setTimezone('Asia/Seoul')->format('H:i');
        $clubs = $this->findNearbyClubs($lat, $lng, $radius);
        $parties = $this->findNearbyParties($lat, $lng, $radius);

        return [
            'totalClubs'    => $clubs->count(),
            'totalParties'  => $parties->count(),
            'openClubs'     => $clubs->filter(fn($c) => $c->isOpenAt($nowTime))->count(),
            'todayParties'  => $parties->filter(fn($p) => $p->event_date->isToday())->count(),
            'nearestArea'   => GeoService::nearestArea($lat, $lng),
        ];
    }

    /**
     * 홈 위젯용 요약
     */
    public function getHomeNearbySummary(float $lat, float $lng, string $sessionId, ?UserPreference $pref): array
    {
        $result = $this->buildNearMeSections($lat, $lng, $sessionId, $pref, null, null, 3.0);

        return [
            'items'       => $result['items']->take(6),
            'total'       => $result['total'],
            'nearestArea' => $result['nearestArea'],
        ];
    }

    private function findNearbyClubs(float $lat, float $lng, float $radius): \Illuminate\Support\Collection
    {
        return Club::active()->get()->filter(function ($club) use ($lat, $lng, $radius) {
            if (!$club->lat || !$club->lng) return false;
            return GeoService::haversineDistance($lat, $lng, $club->lat, $club->lng) <= $radius;
        });
    }

    private function findNearbyParties(float $lat, float $lng, float $radius): \Illuminate\Support\Collection
    {
        return Party::with('club')->upcoming()->get()->filter(function ($party) use ($lat, $lng, $radius) {
            $clubLat = $party->club?->lat;
            $clubLng = $party->club?->lng;
            if (!$clubLat || !$clubLng) return false;
            return GeoService::haversineDistance($lat, $lng, $clubLat, $clubLng) <= $radius;
        });
    }

    private function estimateTravelTime(float $distKm): string
    {
        if ($distKm <= 0.5) return '도보 ' . round($distKm * 1000 / 80) . '분';
        if ($distKm <= 1.5) return '도보 ' . round($distKm / 5 * 60) . '분';
        return round($distKm / 30 * 60) . '분';
    }
}
