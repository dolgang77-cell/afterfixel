<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\CompareItem;
use App\Models\Party;
use App\Models\Club;
use App\Models\RecentView;
use App\Models\SavedFilter;
use App\Services\AvailabilitySignalService;
use App\Services\InquiryConversionService;
use App\Services\ListMapService;
use App\Services\ReviewSummaryService;
use App\Services\VenueFaqService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class PartyController extends Controller
{
    public function __construct(
        private readonly AvailabilitySignalService $availabilitySignals,
        private readonly ListMapService $listMapService,
    ) {}

    public function index(Request $request)
    {
        $currentSort = $request->string('sort')->toString() ?: 'recommended';
        $viewMode = $request->string('view')->toString() === 'map' ? 'map' : 'list';
        $filterPayload = SavedFilter::normalizeFilters('party', [
            'date' => $request->get('date'),
            'area' => $request->get('area'),
            'genre' => $request->get('genre'),
        ]);
        $sortOptions = [
            'recommended' => '추천순',
            'popular' => '인기순',
            'price_low' => '가격 낮은순',
            'response_fast' => '응답 빠른순',
        ];

        $parties = Party::with('club')
            ->withCount(['activeMds as active_md_count'])
            ->upcoming()
            ->onDate($request->get('date'))
            ->inArea($request->get('area'))
            ->inGenre($request->get('genre'))
            ->tap(fn (Builder $query) => $this->applySort($query, $currentSort))
            ->get();

        $cardSummaries = $this->buildCardSummaries($parties);
        $compareItems = CompareItem::resolvedItems($request->session()->getId(), CompareItem::TYPE_PARTY);
        $comparedPartyIds = $compareItems->pluck('id')->all();
        $mapData = $this->listMapService->forParties($parties, $request->get('area'));
        $savedFilter = SavedFilter::currentForViewer($request->session()->getId(), auth()->id(), 'party', $filterPayload);

        // 배치 번역 프리로드
        if (app()->getLocale() !== config('locales.default', 'ko')) {
            $texts = $parties->flatMap(fn($p) => array_filter([
                $p->genre, $p->club?->area,
            ]))->merge(Club::$areas)->merge(Club::$genres)
              ->unique()->values()->toArray();
            \App\Services\AutoTranslator::preloadBatch($texts);
        }

        return view('parties.index', [
            'parties' => $parties,
            'areas'   => Club::$areas,
            'genres'  => Club::$genres,
            'sortOptions' => $sortOptions,
            'currentSort' => array_key_exists($currentSort, $sortOptions) ? $currentSort : 'recommended',
            'viewMode' => $viewMode,
            'filterPayload' => $filterPayload,
            'savedFilterFeatureAvailable' => SavedFilter::available(),
            'savedFilter' => $savedFilter,
            'cardSummaries' => $cardSummaries,
            'compareItems' => $compareItems,
            'comparedPartyIds' => $comparedPartyIds,
            'mapData' => $mapData,
        ]);
    }

    public function show(
        Request $request,
        Party $party,
        InquiryConversionService $inquiryConversion,
        ReviewSummaryService $reviewSummaryService,
        VenueFaqService $venueFaqService,
    )
    {
        $party->recordView();
        $party->load('club', 'activeMds');
        $sessionId = $request->session()->getId();
        $inquiryConversionSummary = $inquiryConversion->summarize('party', $party->id, $party->price_text, $party->activeMds->count());
        $reviewSummary = $reviewSummaryService->buildForTarget('party', $party->id);
        $faqItems = $venueFaqService->forParty($party, $inquiryConversionSummary);

        RecentView::record($sessionId, auth()->id(), 'party', $party->id);
        $isFavorited = Favorite::isFavorited($sessionId, 'party', $party->id);
        $isCompared = CompareItem::isCompared($sessionId, CompareItem::TYPE_PARTY, $party->id);

        // 배치 번역 프리로드
        if (app()->getLocale() !== config('locales.default', 'ko')) {
            $texts = array_filter([
                $party->description, $party->short_description,
                $party->intro_title, $party->lineup, $reviewSummary['summary_text'],
                ...$reviewSummary['all_reviews']->pluck('content')->toArray(),
                ...collect($faqItems)->pluck('answer')->all(),
            ]);
            \App\Services\AutoTranslator::preloadBatch($texts);
        }

        return view('parties.show', [
            'party' => $party,
            'reviews' => $reviewSummary['all_reviews'],
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
                ->orderBy('event_date')
                ->orderBy('start_time'),
            'price_low' => $query
                ->orderByRaw('CASE WHEN ticket_price_min IS NULL THEN 1 ELSE 0 END')
                ->orderBy('ticket_price_min')
                ->orderBy('event_date')
                ->orderBy('start_time'),
            'response_fast' => $query
                ->leftJoinSub($this->responseSpeedSubquery('party'), 'response_metrics', function ($join) {
                    $join->on('response_metrics.target_id', '=', 'parties.id');
                })
                ->select('parties.*')
                ->orderByRaw('CASE WHEN response_metrics.avg_first_reply_minutes IS NULL THEN 1 ELSE 0 END')
                ->orderBy('response_metrics.avg_first_reply_minutes')
                ->orderBy('event_date')
                ->orderBy('start_time'),
            default => $query
                ->orderBy('event_date')
                ->orderBy('start_time')
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

    private function buildCardSummaries(Collection $parties): array
    {
        $signals = $this->availabilitySignals->forTargets(
            $parties->mapWithKeys(fn (Party $party) => [
                'party:' . $party->id => [
                    'type' => 'party',
                    'id' => $party->id,
                    'assigned_md_count' => (int) ($party->active_md_count ?? 0),
                    'start_time' => $party->start_time,
                ],
            ])->all()
        );

        return $parties->mapWithKeys(function (Party $party) use ($signals) {
            $signal = $signals['party:' . $party->id] ?? null;

            $badges = array_values(array_filter([
                $party->event_date?->isToday() ? ['label' => 'TONIGHT', 'variant' => 'pink'] : null,
                ['label' => $party->event_card_label, 'variant' => $party->event_card_variant],
                !empty($signal['availability_signal']['label'])
                    ? [
                        'label' => $signal['availability_signal']['label'],
                        'variant' => $this->signalVariant($signal['availability_signal']['color'] ?? null),
                    ]
                    : null,
                $party->club?->foreigner_allowed ? ['label' => '외국인 OK', 'variant' => 'blue'] : null,
            ]));

            return [
                $party->id => [
                    'badges' => $badges,
                    'price' => $party->price_text,
                    'response_text' => $this->formatResponseTime($signal['avg_first_reply_minutes'] ?? null),
                    'support_text' => $party->event_date->format('n/j') . ' · ' . $party->time_range_text . ' · ' . $party->event_card_label,
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
