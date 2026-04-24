<?php

namespace App\Services;

use App\Models\Club;
use App\Models\Party;
use Illuminate\Support\Collection;

class ListMapService
{
    public function forClubs(Collection $clubs, ?string $area = null): array
    {
        return $this->build(
            $clubs->map(function (Club $club) {
                return [
                    'id' => $club->id,
                    'name' => $club->name,
                    'lat' => $club->lat,
                    'lng' => $club->lng,
                    'href' => route('clubs.show', $club),
                    'meta' => trim(implode(' · ', array_filter([
                        $club->area,
                        $club->genre,
                    ]))),
                    'price' => $club->price_text,
                    'badge' => $club->foreigner_allowed ? '외국인 OK' : null,
                ];
            }),
            $area
        );
    }

    public function forParties(Collection $parties, ?string $area = null): array
    {
        return $this->build(
            $parties->map(function (Party $party) {
                return [
                    'id' => $party->id,
                    'name' => $party->name,
                    'lat' => $party->club?->lat,
                    'lng' => $party->club?->lng,
                    'href' => route('parties.show', $party),
                    'meta' => trim(implode(' · ', array_filter([
                        $party->club?->area,
                        $party->genre,
                        $party->event_card_label,
                        $party->event_date?->format('n.j'),
                    ]))),
                    'price' => $party->price_text,
                    'badge' => $party->event_date?->isToday() ? 'TONIGHT' : $party->event_card_label,
                ];
            }),
            $area
        );
    }

    private function build(Collection $items, ?string $area = null): array
    {
        $totalCount = $items->count();
        $points = $items
            ->filter(fn (array $item) => is_numeric($item['lat']) && is_numeric($item['lng']))
            ->values();
        $missingCount = $totalCount - $points->count();
        $areaCenter = $area ? GeoService::areaToCoords($area) : null;

        if ($points->isEmpty()) {
            return [
                'points' => collect(),
                'count' => 0,
                'missing_count' => $missingCount,
                'has_points' => false,
                'area_center' => $areaCenter,
            ];
        }

        $latitudes = $points->pluck('lat')->map(fn ($value) => (float) $value);
        $longitudes = $points->pluck('lng')->map(fn ($value) => (float) $value);
        $minLat = $latitudes->min();
        $maxLat = $latitudes->max();
        $minLng = $longitudes->min();
        $maxLng = $longitudes->max();
        $latSpan = max($maxLat - $minLat, 0.01);
        $lngSpan = max($maxLng - $minLng, 0.01);

        $normalizedPoints = $points->map(function (array $point, int $index) use ($minLat, $minLng, $latSpan, $lngSpan) {
            $x = 12 + (((float) $point['lng'] - $minLng) / $lngSpan) * 76;
            $y = 14 + (((float) $point['lat'] - $minLat) / $latSpan) * 68;

            return $point + [
                'x' => round($x, 2),
                'y' => round(100 - $y, 2),
                'index' => $index + 1,
            ];
        });

        return [
            'points' => $normalizedPoints,
            'count' => $normalizedPoints->count(),
            'missing_count' => $missingCount,
            'has_points' => $normalizedPoints->isNotEmpty(),
            'area_center' => $areaCenter,
        ];
    }
}
