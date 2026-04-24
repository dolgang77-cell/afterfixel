<?php

namespace App\Http\Controllers;

use App\Http\Requests\TourRecommendRequest;
use App\Models\Club;
use App\Models\Favorite;
use App\Models\RecentView;
use App\Models\UserPreference;
use App\Services\TourRecommendationService;
use Illuminate\Http\Request;

class TourController extends Controller
{
    public function __construct(
        private readonly TourRecommendationService $tourService,
    ) {}

    public function index(Request $request)
    {
        $sessionId = $request->session()->getId();
        $pref = UserPreference::where('session_id', $sessionId)->first();

        // 배치 번역 프리로드 (area/genre 라디오 버튼)
        if (app()->getLocale() !== config('locales.default', 'ko')) {
            \App\Services\AutoTranslator::preloadBatch(
                array_merge(Club::$areas, Club::$genres)
            );
        }

        return view('tour.index', [
            'areas'  => Club::$areas,
            'genres' => Club::$genres,
            'pref'   => $pref,
        ]);
    }

    public function recommend(TourRecommendRequest $request)
    {
        $validated = $request->validated();
        $sessionId = $request->session()->getId();

        // 사용자 선호도를 추천에 반영
        $pref = UserPreference::where('session_id', $sessionId)->first();
        if ($pref) {
            $validated['_user_pref'] = [
                'preferred_genres' => $pref->preferred_genres ?? [],
                'preferred_areas'  => $pref->preferred_areas ?? [],
                'foreigner_mode'   => $pref->foreigner_mode,
                'favorite_club_ids' => Favorite::forSession($sessionId)
                    ->ofType('club')->pluck('target_id')->toArray(),
            ];
        }

        $results = $this->tourService->recommend($validated);

        // 추천 결과 최근 본으로 기록
        if (!empty($results['recommendation'])) {
            $rec = $results['recommendation'];
            if ($rec->id ?? null) {
                RecentView::record($sessionId, auth()->id(), 'tour', $rec->id);
            }
        }

        return view('tour.result', [
            'results'   => $results,
            'validated' => $validated,
            'areas'     => Club::$areas,
            'genres'    => Club::$genres,
            'hasUserPref' => $pref?->hasPreferences() ?? false,
        ]);
    }
}
