<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Club;
use App\Models\CommunityPost;
use App\Models\Party;
use App\Models\UserPreference;
use App\Services\RevisitHubService;
use App\Services\TonightService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct(
        private readonly TonightService $tonight,
        private readonly RevisitHubService $revisitHub,
    ) {}

    public function index(Request $request)
    {
        $sessionId = $request->session()->getId();
        $pref = UserPreference::where('session_id', $sessionId)->first();

        // 배너
        $banners = Banner::active()->position('home_top')->orderBy('sort_order')->get();

        // 기본 데이터
        $todayParties = Party::with('club')->today()->upcoming()->orderBy('start_time')->get();
        $hotClubs     = Club::active()->orderByDesc('rating_avg')->limit(6)->get();
        $recentPosts  = CommunityPost::with('club')->visible()->recent()->limit(5)->get();

        // ── 오늘밤 추천 ──
        $tonightSummary = $this->tonight->getHomeTonightSummary($sessionId, $pref);
        $tonightStatus  = $this->tonight->getStatusSummary();

        // ── 개인화 데이터 ──
        $personalizedClubs = collect();
        $personalizedParties = collect();
        $heroSecondary = null;
        $heroHighlights = collect();

        $hasPreferences = $pref?->hasPreferences() ?? false;
        $revisitHub = $this->revisitHub->build($sessionId, $request->user());

        if ($hasPreferences) {
            $personalizedClubs = Club::active()
                ->when(!empty($pref->preferred_areas), fn($q) => $q->whereIn('area', $pref->preferred_areas))
                ->when(!empty($pref->preferred_genres), fn($q) => $q->whereIn('genre', $pref->preferred_genres))
                ->when($pref->foreigner_mode, fn($q) => $q->foreignerFriendly())
                ->when($pref->budget_max, fn($q) => $q->where('entry_fee_min', '<=', $pref->budget_max))
                ->orderByDesc('rating_avg')
                ->limit(6)
                ->get();

            $personalizedParties = Party::with('club')
                ->upcoming()
                ->when(!empty($pref->preferred_genres), fn($q) => $q->whereIn('genre', $pref->preferred_genres))
                ->when(!empty($pref->preferred_areas), fn($q) => $q->inArea($pref->preferred_areas[0] ?? null))
                ->orderBy('event_date')
                ->limit(6)
                ->get();
        }

        $heroSecondary = [
            'url' => route('parties.index', ['date' => today()->toDateString()]),
            'label' => '오늘 파티 보기',
            'eyebrow' => '빠른 진입',
            'title' => '오늘 열리는 전국 파티부터 보기',
            'meta' => '서울·부산·제주까지 날짜 기준으로 빠르게 탐색',
        ];

        $heroHighlights = collect([
            [
                'label' => '오늘 파티',
                'value' => (string) $tonightStatus['todayPartyCount'],
                'url' => route('parties.index', ['date' => today()->toDateString()]),
                'tone' => 'pink',
            ],
            [
                'label' => '지금 추천',
                'value' => (string) $tonightSummary['items']->count(),
                'url' => route('tonight.index'),
                'tone' => 'emerald',
            ],
            [
                'label' => '인기 클럽',
                'value' => (string) $hotClubs->count(),
                'url' => route('clubs.index'),
                'tone' => 'sky',
            ],
        ]);

        // 홈 페이지 번역 배치 프리로드
        if (app()->getLocale() !== config('locales.default', 'ko')) {
            $texts = collect()
                ->merge($hotClubs->flatMap(fn($c) => [$c->area, $c->genre]))
                ->merge($todayParties->flatMap(fn($p) => [$p->genre, $p->club?->area]))
                ->merge($recentPosts->flatMap(fn($p) => [$p->title, $p->content]))
                ->filter()->unique()->values()->toArray();
            \App\Services\AutoTranslator::preloadBatch($texts);
        }

        return view('home', compact(
            'banners',
            'todayParties', 'hotClubs', 'recentPosts',
            'personalizedClubs', 'personalizedParties',
            'revisitHub', 'hasPreferences',
            'tonightSummary', 'tonightStatus',
            'heroSecondary', 'heroHighlights'
        ));
    }
}
