<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\CompareItem;
use App\Models\Favorite;
use App\Models\RecentView;
use App\Models\SavedFilter;
use App\Services\AutoTranslator;
use App\Services\AvailabilitySignalService;
use App\Services\InquiryConversionService;
use App\Services\ListMapService;
use App\Services\ReviewSummaryService;
use App\Services\VenueFaqService;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClubController extends Controller
{
    public function __construct(
        private readonly AvailabilitySignalService $availabilitySignals,
        private readonly ListMapService $listMapService,
    ) {}

    public function index(Request $request)
    {
        $currentSort = $request->string('sort')->toString() ?: 'recommended';
        $viewMode = $request->string('view')->toString() === 'map' ? 'map' : 'list';
        $filterPayload = SavedFilter::normalizeFilters('club', [
            'area' => $request->get('area'),
            'genre' => $request->get('genre'),
            'foreigner' => $request->boolean('foreigner'),
        ]);
        $sortOptions = [
            'recommended' => '추천순',
            'popular' => '인기순',
            'price_low' => '가격 낮은순',
            'response_fast' => '응답 빠른순',
        ];

        $clubs = Club::query()
            ->active()
            ->withCount(['activeMds as active_md_count'])
            ->inArea($request->get('area'))
            ->inGenre($request->get('genre'))
            ->when($request->get('foreigner'), fn($q) => $q->foreignerFriendly())
            ->tap(fn (Builder $query) => $this->applySort($query, $currentSort))
            ->get();

        $cardSummaries = $this->buildCardSummaries($clubs);
        $compareItems = CompareItem::resolvedItems($request->session()->getId(), CompareItem::TYPE_CLUB);
        $comparedClubIds = $compareItems->pluck('id')->all();
        $mapData = $this->listMapService->forClubs($clubs, $request->get('area'));
        $savedFilter = SavedFilter::currentForViewer($request->session()->getId(), auth()->id(), 'club', $filterPayload);

        // 리스트 전체 area/genre를 배치 번역 프리로드 (카드별 개별 API 호출 방지)
        if (app()->getLocale() !== config('locales.default', 'ko')) {
            $texts = $clubs->flatMap(fn($c) => array_filter([
                $c->area, $c->genre, $c->subgenre, $c->vibe,
            ]))->merge(Club::$areas)->merge(Club::$genres)
              ->unique()->values()->toArray();
            AutoTranslator::preloadBatch($texts);
        }

        return view('clubs.index', [
            'clubs'  => $clubs,
            'areas'  => Club::$areas,
            'genres' => Club::$genres,
            'sortOptions' => $sortOptions,
            'currentSort' => array_key_exists($currentSort, $sortOptions) ? $currentSort : 'recommended',
            'viewMode' => $viewMode,
            'filterPayload' => $filterPayload,
            'savedFilterFeatureAvailable' => SavedFilter::available(),
            'savedFilter' => $savedFilter,
            'cardSummaries' => $cardSummaries,
            'compareItems' => $compareItems,
            'comparedClubIds' => $comparedClubIds,
            'mapData' => $mapData,
        ]);
    }

    public function show(
        Request $request,
        Club $club,
        InquiryConversionService $inquiryConversion,
        ReviewSummaryService $reviewSummaryService,
        VenueFaqService $venueFaqService,
    )
    {
        $club->recordView();
        $club->load('upcomingParties', 'activeMds');
        $sessionId = $request->session()->getId();
        $inquiryConversionSummary = $inquiryConversion->summarize('club', $club->id, $club->price_text, $club->activeMds->count());
        $reviewSummary = $reviewSummaryService->buildForTarget(
            'club',
            $club->id,
            $club->rating_avg,
            $club->rating_count,
            $club->rating_summary,
        );
        $faqItems = $venueFaqService->forClub($club, $inquiryConversionSummary);

        RecentView::record($sessionId, auth()->id(), 'club', $club->id);
        $isFavorited = Favorite::isFavorited($sessionId, 'club', $club->id);
        $isCompared = CompareItem::isCompared($sessionId, CompareItem::TYPE_CLUB, $club->id);

        // 상세 페이지 전체 텍스트 배치 번역 프리로드
        if (app()->getLocale() !== config('locales.default', 'ko')) {
            $texts = array_filter([
                $club->description, $club->short_description,
                $club->intro_title, $reviewSummary['summary_text'],
                $club->area, $club->genre, $club->subgenre, $club->vibe,
                ...$reviewSummary['all_reviews']->pluck('content')->toArray(),
                ...collect($faqItems)->pluck('answer')->all(),
            ]);
            AutoTranslator::preloadBatch($texts);
        }

        return view('clubs.show', [
            'club'        => $club,
            'reviews'     => $reviewSummary['all_reviews'],
            'reviewSummary' => $reviewSummary,
            'faqItems' => $faqItems,
            'isFavorited' => $isFavorited,
            'isCompared' => $isCompared,
            'inquiryConversion' => $inquiryConversionSummary,
        ]);
    }

    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'popular' => $query
                ->orderByDesc('view_count')
                ->orderByDesc('rating_avg')
                ->orderBy('sort_order'),
            'price_low' => $query
                ->orderByRaw('CASE WHEN entry_fee_min IS NULL THEN 1 ELSE 0 END')
                ->orderBy('entry_fee_min')
                ->orderByDesc('rating_avg'),
            'response_fast' => $query
                ->leftJoinSub($this->responseSpeedSubquery('club'), 'response_metrics', function ($join) {
                    $join->on('response_metrics.target_id', '=', 'clubs.id');
                })
                ->select('clubs.*')
                ->orderByRaw('CASE WHEN response_metrics.avg_first_reply_minutes IS NULL THEN 1 ELSE 0 END')
                ->orderBy('response_metrics.avg_first_reply_minutes')
                ->orderByDesc('rating_avg'),
            default => $query
                ->orderBy('sort_order')
                ->orderByDesc('rating_avg')
                ->orderByDesc('view_count'),
        };
    }

    private function responseSpeedSubquery(string $targetType)
    {
        $firstReplies = DB::table('inquiry_replies')
            ->select('inquiry_id', DB::raw('MIN(created_at) as first_reply_at'))
            ->whereIn('author_type', ['md', 'admin'])
            ->groupBy('inquiry_id');

        return DB::table('inquiries')
            ->leftJoinSub($firstReplies, 'first_replies', function ($join) {
                $join->on('first_replies.inquiry_id', '=', 'inquiries.id');
            })
            ->select('target_id', DB::raw('AVG(TIMESTAMPDIFF(MINUTE, inquiries.created_at, first_replies.first_reply_at)) as avg_first_reply_minutes'))
            ->where('target_type', $targetType)
            ->where('created_at', '>=', now()->subDays(30))
            ->whereNotNull('first_replies.first_reply_at')
            ->groupBy('target_id');
    }

    private function buildCardSummaries(Collection $clubs): array
    {
        $signals = $this->availabilitySignals->forTargets(
            $clubs->mapWithKeys(fn (Club $club) => [
                'club:' . $club->id => [
                    'type' => 'club',
                    'id' => $club->id,
                    'assigned_md_count' => (int) ($club->active_md_count ?? 0),
                    'open_time' => $club->open_time,
                ],
            ])->all()
        );

        return $clubs->mapWithKeys(function (Club $club) use ($signals) {
            $signal = $signals['club:' . $club->id] ?? null;
            $isOpen = $club->open_time && $club->close_time
                ? $club->isOpenAt(now()->format('H:i'))
                : null;

            $badges = array_values(array_filter([
                $isOpen === true ? ['label' => '지금 영업중', 'variant' => 'green'] : null,
                $isOpen === false ? ['label' => '오늘 방문 추천', 'variant' => 'default'] : null,
                !empty($signal['availability_signal']['label'])
                    ? [
                        'label' => $signal['availability_signal']['label'],
                        'variant' => $this->signalVariant($signal['availability_signal']['color'] ?? null),
                    ]
                    : null,
                $club->foreigner_allowed ? ['label' => '외국인 OK', 'variant' => 'blue'] : null,
            ]));

            return [
                $club->id => [
                    'badges' => $badges,
                    'price' => $club->price_text,
                    'response_text' => $this->formatResponseTime($signal['avg_first_reply_minutes'] ?? null),
                    'support_text' => $club->operating_hours_text,
                    'highlight_text' => $signal['best_visit_window'] ?? ($signal['crowd_signal']['label'] ?? null),
                ],
            ];
        })->all();
    }

    private function formatResponseTime(?int $minutes): string
    {
        if ($minutes === null) {
            return '응답 데이터 준비중';
        }

        if ($minutes < 60) {
            return '평균 ' . $minutes . '분';
        }

        $hours = intdiv($minutes, 60);
        $remain = $minutes % 60;

        if ($remain === 0) {
            return '평균 ' . $hours . '시간';
        }

        return '평균 ' . $hours . '시간 ' . $remain . '분';
    }

    private function signalVariant(?string $color): string
    {
        return match ($color) {
            'green' => 'green',
            'cyan' => 'cyan',
            'orange' => 'orange',
            'yellow' => 'yellow',
            'purple' => 'purple',
            'blue' => 'blue',
            default => 'default',
        };
    }
}
