<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\CompareItem;
use App\Models\Party;
use App\Services\InquiryConversionService;
use Illuminate\Http\Request;

class CompareController extends Controller
{
    public function index(Request $request, string $type, InquiryConversionService $inquiryConversion)
    {
        abort_unless(in_array($type, [CompareItem::TYPE_CLUB, CompareItem::TYPE_PARTY], true), 404);

        $sessionId = $request->session()->getId();
        $items = CompareItem::resolvedItems($sessionId, $type);

        $comparisonRows = $items->map(function ($item) use ($type, $inquiryConversion) {
            $summary = $inquiryConversion->summarize($type, $item->id, $item->price_text, (int) ($item->active_md_count ?? 0));

            if ($type === CompareItem::TYPE_CLUB) {
                return [
                    'item' => $item,
                    'detail_url' => route('clubs.show', $item),
                    'image_url' => $item->thumbnail_url,
                    'headline' => $item->name,
                    'subline' => trim($item->area . ' · ' . $item->genre . ($item->subgenre ? ' / ' . $item->subgenre : '')),
                    'facts' => [
                        '가격대' => $item->price_text,
                        '평점' => number_format((float) $item->rating_avg, 1),
                        '응답 속도' => $summary['response_time_text'],
                        '문의 가능' => $summary['availability_signal']['label'],
                        '외국인' => $item->foreigner_allowed ? '가능' : '안내 필요',
                        '운영 시간' => trim(($item->open_time ?? '') . ' ~ ' . ($item->close_time ?? '')),
                    ],
                ];
            }

            return [
                'item' => $item,
                'detail_url' => route('parties.show', $item),
                'image_url' => $item->thumbnail_url,
                'headline' => $item->name,
                'subline' => trim(($item->club?->name ? $item->club->name . ' · ' : '') . ($item->event_date?->format('n/j') ?? '') . ' · ' . $item->genre . ' · ' . $item->event_card_label),
                'facts' => [
                    '일정' => ($item->event_date?->format('n/j') ?? '-') . ' ' . ($item->start_time ?? ''),
                    '구분' => $item->event_card_label,
                    '가격대' => $item->price_text,
                    '응답 속도' => $summary['response_time_text'],
                    '문의 가능' => $summary['availability_signal']['label'],
                    '장소' => $item->club?->name ?? '안내 예정',
                    '외국인' => $item->club?->foreigner_allowed ? '가능' : '안내 필요',
                ],
            ];
        })->values();

        $comparisonLabels = $type === CompareItem::TYPE_CLUB
            ? ['가격대', '평점', '응답 속도', '문의 가능', '외국인', '운영 시간']
            : ['일정', '구분', '가격대', '응답 속도', '문의 가능', '장소', '외국인'];

        return view('compare.index', [
            'type' => $type,
            'items' => $items,
            'comparisonRows' => $comparisonRows,
            'comparisonLabels' => $comparisonLabels,
            'maxItems' => CompareItem::MAX_ITEMS,
        ]);
    }

    public function toggle(Request $request, string $type, int $id)
    {
        abort_unless(in_array($type, [CompareItem::TYPE_CLUB, CompareItem::TYPE_PARTY], true), 404);

        $modelClass = $type === CompareItem::TYPE_CLUB ? Club::class : Party::class;
        $modelClass::query()->findOrFail($id);

        $sessionId = $request->session()->getId();
        $result = CompareItem::toggle($sessionId, auth()->id(), $type, $id);

        if ($request->expectsJson()) {
            $status = $result['limit_reached'] ? 422 : 200;

            return response()->json([
                'ok' => !$result['limit_reached'],
                'result' => $result,
                'count' => count(CompareItem::comparedIds($sessionId, $type)),
            ], $status);
        }

        if ($result['limit_reached']) {
            return back()->with('error', '비교함은 최대 4개까지 담을 수 있습니다.');
        }

        if (!empty($result['unavailable'])) {
            return back()->with('error', '비교함 테이블이 아직 준비되지 않았습니다. 마이그레이션 적용 후 다시 시도해 주세요.');
        }

        return back()->with('success', $result['added'] ? '비교함에 추가했습니다.' : '비교함에서 제거했습니다.');
    }

    public function clear(Request $request, string $type)
    {
        abort_unless(in_array($type, [CompareItem::TYPE_CLUB, CompareItem::TYPE_PARTY], true), 404);

        CompareItem::clear($request->session()->getId(), $type);

        return back()->with('success', '비교함을 비웠습니다.');
    }
}
