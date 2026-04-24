<?php

namespace App\Services;

use App\Models\Inquiry;
use Illuminate\Support\Collection;

class InquiryConversionService
{
    public function __construct(private readonly AvailabilitySignalService $availabilitySignals)
    {
    }

    public function summarize(string $targetType, int $targetId, ?string $fallbackPriceText = null, int $assignedMdCount = 0): array
    {
        $recentInquiries = Inquiry::with([
            'publicReplies' => fn ($query) => $query
                ->whereIn('author_type', ['md', 'admin'])
                ->orderBy('created_at'),
        ])
            ->where('target_type', $targetType)
            ->where('target_id', $targetId)
            ->where('created_at', '>=', now()->subDays(30))
            ->get();

        $avgFirstReplyMinutes = $this->averageFirstReplyMinutes($recentInquiries);
        $confirmationRate = $recentInquiries->isNotEmpty()
            ? (int) round(($recentInquiries->where('status', 'reservation_confirmed')->count() / $recentInquiries->count()) * 100)
            : null;

        [$budgetGuideText, $budgetGuideMin, $budgetGuideMax] = $this->budgetGuide($recentInquiries, $fallbackPriceText);
        $availability = $this->availabilitySignals->forTarget([
            'type' => $targetType,
            'id' => $targetId,
            'assigned_md_count' => $assignedMdCount,
        ]);

        return [
            'avg_first_reply_minutes' => $avgFirstReplyMinutes,
            'response_time_text' => $this->formatReplyTime($avgFirstReplyMinutes),
            'response_hint' => $this->responseHint($avgFirstReplyMinutes, $assignedMdCount),
            'confirmation_rate' => $confirmationRate,
            'confirmation_text' => $confirmationRate === null
                ? '최근 확정 데이터 준비중'
                : '최근 30일 문의 기준',
            'budget_guide_text' => $budgetGuideText,
            'budget_guide_min' => $budgetGuideMin,
            'budget_guide_max' => $budgetGuideMax,
            'recent_inquiry_count' => $recentInquiries->count(),
            'assigned_md_count' => $assignedMdCount,
            'availability_signal' => $availability['availability_signal'],
            'crowd_signal' => $availability['crowd_signal'],
            'best_visit_window' => $availability['best_visit_window'],
            'availability_summary' => $availability['availability_summary'],
        ];
    }

    private function averageFirstReplyMinutes(Collection $inquiries): ?int
    {
        $durations = $inquiries
            ->map(function (Inquiry $inquiry) {
                $firstReply = $inquiry->publicReplies->first();

                if (!$firstReply) {
                    return null;
                }

                return max(1, $inquiry->created_at->diffInMinutes($firstReply->created_at));
            })
            ->filter()
            ->values();

        if ($durations->isEmpty()) {
            return null;
        }

        return (int) round($durations->avg());
    }

    private function budgetGuide(Collection $inquiries, ?string $fallbackPriceText): array
    {
        $midpoints = $inquiries
            ->map(function (Inquiry $inquiry) {
                if ($inquiry->budget_min !== null && $inquiry->budget_max !== null) {
                    return (int) round(($inquiry->budget_min + $inquiry->budget_max) / 2);
                }

                return $inquiry->budget_max ?? $inquiry->budget_min;
            })
            ->filter(fn ($value) => $value !== null && $value > 0)
            ->sort()
            ->values();

        if ($midpoints->isNotEmpty()) {
            $median = (int) round($midpoints->median());
            $spread = $median >= 300000 ? 200000 : ($median >= 150000 ? 100000 : 50000);
            $min = $this->roundBudget(max(0, $median - $spread));
            $max = $this->roundBudget($median + $spread);

            return ['최근 문의 예산 기준 약 ' . number_format($min) . ' ~ ' . number_format($max) . '원', $min, $max];
        }

        if ($fallbackPriceText) {
            return ['일반 안내 가격 ' . $fallbackPriceText, null, null];
        }

        return ['예상 패키지 가격 데이터 준비중', null, null];
    }

    private function formatReplyTime(?int $minutes): string
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

    private function responseHint(?int $minutes, int $assignedMdCount): string
    {
        if ($minutes === null) {
            return $assignedMdCount > 0
                ? '활성 MD ' . $assignedMdCount . '명 연결'
                : '응답 담당자 배정 확인 필요';
        }

        if ($minutes <= 15) {
            return '지금 문의 빠름';
        }

        if ($minutes <= 60) {
            return '오늘 답변 흐름 보통';
        }

        return '답변 확인에 시간이 걸릴 수 있습니다';
    }

    private function roundBudget(int $value): int
    {
        return (int) (round($value / 10000) * 10000);
    }
}
