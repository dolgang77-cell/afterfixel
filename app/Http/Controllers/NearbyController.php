<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\UserPreference;
use App\Services\GeoService;
use App\Services\NearbyService;
use Illuminate\Http\Request;

class NearbyController extends Controller
{
    public function __construct(
        private readonly NearbyService $nearby,
    ) {}

    /**
     * Near Me 전용 페이지
     */
    public function index(Request $request)
    {
        $sessionId = $request->session()->getId();
        $pref = UserPreference::where('session_id', $sessionId)->first();

        $lat    = $request->float('lat');
        $lng    = $request->float('lng');
        $area   = $request->get('area');
        $genre  = $request->get('genre');
        $budget = $request->integer('budget') ?: null;
        $radius = $request->float('radius', 5.0);

        // 좌표가 없고 지역이 선택된 경우 → 지역 중심 좌표 사용
        if ((!$lat || !$lng) && $area) {
            $coords = GeoService::areaToCoords($area);
            if ($coords) {
                $lat = $coords['lat'];
                $lng = $coords['lng'];
            }
        }

        $hasLocation = $lat && $lng;
        $result = null;
        $status = null;

        if ($hasLocation) {
            $result = $this->nearby->buildNearMeSections($lat, $lng, $sessionId, $pref, $genre, $budget, $radius);
            $status = $this->nearby->getNearbyStatusSummary($lat, $lng, min($radius, 3.0));
        }

        return view('nearby.index', [
            'result'      => $result,
            'status'      => $status,
            'pref'        => $pref,
            'hasLocation' => $hasLocation,
            'userLat'     => $lat,
            'userLng'     => $lng,
            'selectedArea' => $area,
            'filters'     => compact('genre', 'budget', 'radius'),
            'areas'       => Club::$areas,
            'genres'      => Club::$genres,
            'areaCenters' => GeoService::AREA_CENTERS,
        ]);
    }

    /**
     * 빠른 추천 (JSON)
     */
    public function recommend(Request $request)
    {
        $sessionId = $request->session()->getId();
        $pref = UserPreference::where('session_id', $sessionId)->first();

        $lat    = $request->float('lat');
        $lng    = $request->float('lng');
        $area   = $request->get('area');
        $genre  = $request->get('genre');
        $budget = $request->integer('budget') ?: null;
        $radius = $request->float('radius', 5.0);

        if ((!$lat || !$lng) && $area) {
            $coords = GeoService::areaToCoords($area);
            if ($coords) {
                $lat = $coords['lat'];
                $lng = $coords['lng'];
            }
        }

        if (!$lat || !$lng) {
            if ($request->expectsJson()) {
                return response()->json(['error' => '위치 정보가 필요합니다.'], 422);
            }
            return redirect()->route('nearby.index')->with('error', '위치 정보가 필요합니다.');
        }

        $result = $this->nearby->buildNearMeSections($lat, $lng, $sessionId, $pref, $genre, $budget, $radius);
        $status = $this->nearby->getNearbyStatusSummary($lat, $lng, min($radius, 3.0));

        if ($request->expectsJson()) {
            return response()->json(['result' => $result, 'status' => $status]);
        }

        return view('nearby.index', [
            'result'      => $result,
            'status'      => $status,
            'pref'        => $pref,
            'hasLocation' => true,
            'userLat'     => $lat,
            'userLng'     => $lng,
            'selectedArea' => $area,
            'filters'     => compact('genre', 'budget', 'radius'),
            'areas'       => Club::$areas,
            'genres'      => Club::$genres,
            'areaCenters' => GeoService::AREA_CENTERS,
        ]);
    }

    /**
     * Near Me 상태 요약 (JSON) - 홈 위젯용
     */
    public function summary(Request $request)
    {
        $lat = $request->float('lat');
        $lng = $request->float('lng');

        if (!$lat || !$lng) {
            return response()->json(['error' => 'no_location'], 422);
        }

        $sessionId = $request->session()->getId();
        $pref = UserPreference::where('session_id', $sessionId)->first();

        $summary = $this->nearby->getHomeNearbySummary($lat, $lng, $sessionId, $pref);
        $status  = $this->nearby->getNearbyStatusSummary($lat, $lng);

        return response()->json([
            'summary' => $summary,
            'status'  => $status,
        ]);
    }

    /**
     * 위치 저장 (세션 기반)
     */
    public function saveLocation(Request $request)
    {
        $lat = $request->float('lat');
        $lng = $request->float('lng');

        if (!$lat || !$lng) {
            return response()->json(['error' => 'invalid'], 422);
        }

        $request->session()->put('user_lat', $lat);
        $request->session()->put('user_lng', $lng);
        $request->session()->put('user_area', GeoService::nearestArea($lat, $lng));

        return response()->json([
            'success' => true,
            'area' => GeoService::nearestArea($lat, $lng),
        ]);
    }
}
