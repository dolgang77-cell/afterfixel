<?php

namespace App\Services;

use App\Models\Inquiry;
use App\Models\InquiryReply;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AvailabilitySignalService
{
    public function forTarget(array $target): array
    {
        $key = $this->key($target['type'], $target['id']);

        return $this->forTargets([$key => $target])[$key] ?? $this->fallbackSignal($target);
    }

    public function forTargets(array $targets): array
    {
        if (empty($targets)) {
            return [];
        }

        $clubIds = [];
        $partyIds = [];

        foreach ($targets as $target) {
            if (($target['type'] ?? null) === 'club') {
                $clubIds[] = (int) $target['id'];
            }

            if (($target['type'] ?? null) === 'party') {
                $partyIds[] = (int) $target['id'];
            }
        }

        $inquiries = Inquiry::with([
            'publicReplies' => fn ($query) => $query
                ->whereIn('author_type', ['md', 'admin'])
                ->where('created_at', '>=', now()->subDays(7))
                ->orderBy('created_at'),
        ])
            ->where('created_at', '>=', now()->subDays(7))
            ->where(function ($query) use ($clubIds, $partyIds) {
                if (!empty($clubIds)) {
                    $query->where(function ($sub) use ($clubIds) {
                        $sub->where('target_type', 'club')->whereIn('target_id', $clubIds);
                    });
                }

                if (!empty($partyIds)) {
                    $method = empty($clubIds) ? 'where' : 'orWhere';
                    $query->{$method}(function ($sub) use ($partyIds) {
                        $sub->where('target_type', 'party')->whereIn('target_id', $partyIds);
                    });
                }
            })
            ->get()
            ->groupBy(fn (Inquiry $inquiry) => $this->key($inquiry->target_type, $inquiry->target_id));

        $signals = [];

        foreach ($targets as $key => $target) {
            $signals[$key] = $this->buildSignal($target, $inquiries->get($key, collect()));
        }

        return $signals;
    }

    private function buildSignal(array $target, Collection $inquiries): array
    {
        $todayStart = now()->startOfDay();
        $recentReplyCutoff = now()->subMinutes(30);

        $todayInquiries = $inquiries->filter(fn (Inquiry $inquiry) => $inquiry->created_at >= $todayStart);
        $recentReplyCount = $inquiries->sum(
            fn (Inquiry $inquiry) => $inquiry->publicReplies->filter(fn (InquiryReply $reply) => $reply->created_at >= $recentReplyCutoff)->count()
        );
        $confirmationsToday = $inquiries->filter(
            fn (Inquiry $inquiry) => $inquiry->status === 'reservation_confirmed' && $inquiry->updated_at >= $todayStart
        )->count();

        $firstReplyMinutes = $inquiries
            ->map(function (Inquiry $inquiry) {
                $firstReply = $inquiry->publicReplies->first();

                if (!$firstReply) {
                    return null;
                }

                return max(1, $inquiry->created_at->diffInMinutes($firstReply->created_at));
            })
            ->filter()
            ->values();

        $avgFirstReplyMinutes = $firstReplyMinutes->isNotEmpty()
            ? (int) round($firstReplyMinutes->avg())
            : null;

        $assignedMdCount = (int) ($target['assigned_md_count'] ?? 0);
        $availabilityScore = $this->availabilityScore($avgFirstReplyMinutes, $recentReplyCount, $assignedMdCount);
        $crowdScore = min(100, (int) ($todayInquiries->count() * 12 + $confirmationsToday * 18));

        $availabilitySignal = $this->availabilitySignal($availabilityScore, $avgFirstReplyMinutes, $recentReplyCount, $assignedMdCount);
        $crowdSignal = $this->crowdSignal($crowdScore, $todayInquiries->count(), $confirmationsToday);
        $bestVisitWindow = $this->bestVisitWindow($target);

        return [
            'availability_score' => $availabilityScore,
            'availability_signal' => $availabilitySignal,
            'crowd_score' => $crowdScore,
            'crowd_signal' => $crowdSignal,
            'best_visit_window' => $bestVisitWindow,
            'today_inquiry_count' => $todayInquiries->count(),
            'recent_reply_count' => $recentReplyCount,
            'confirmations_today' => $confirmationsToday,
            'avg_first_reply_minutes' => $avgFirstReplyMinutes,
            'availability_summary' => implode(' · ', array_filter([
                $availabilitySignal['label'] ?? null,
                $crowdSignal['label'] ?? null,
                $bestVisitWindow,
            ])),
        ];
    }

    private function availabilityScore(?int $avgFirstReplyMinutes, int $recentReplyCount, int $assignedMdCount): int
    {
        $score = 20;

        if ($assignedMdCount > 0) {
            $score += 15;
        }

        if ($recentReplyCount >= 2) {
            $score += 40;
        } elseif ($recentReplyCount === 1) {
            $score += 25;
        }

        if ($avgFirstReplyMinutes !== null) {
            if ($avgFirstReplyMinutes <= 15) {
                $score += 25;
            } elseif ($avgFirstReplyMinutes <= 60) {
                $score += 15;
            } elseif ($avgFirstReplyMinutes <= 180) {
                $score += 5;
            } else {
                $score -= 10;
            }
        }

        return max(0, min(100, $score));
    }

    private function availabilitySignal(int $score, ?int $avgFirstReplyMinutes, int $recentReplyCount, int $assignedMdCount): array
    {
        if ($score >= 70 || $recentReplyCount >= 2) {
            return ['label' => '지금 문의 빠름', 'color' => 'green'];
        }

        if ($score >= 45 || ($assignedMdCount > 0 && $avgFirstReplyMinutes !== null && $avgFirstReplyMinutes <= 60)) {
            return ['label' => '답변 흐름 보통', 'color' => 'cyan'];
        }

        if ($assignedMdCount > 0) {
            return ['label' => '답변 확인 필요', 'color' => 'orange'];
        }

        return ['label' => '문의 가능', 'color' => 'gray'];
    }

    private function crowdSignal(int $score, int $todayInquiries, int $confirmationsToday): array
    {
        if ($score >= 75 || $todayInquiries >= 6 || $confirmationsToday >= 3) {
            return ['label' => '오늘 피크 예상', 'color' => 'purple'];
        }

        if ($score >= 35 || $todayInquiries >= 3) {
            return ['label' => '오늘 문의 많음', 'color' => 'yellow'];
        }

        return ['label' => '오늘 비교적 여유', 'color' => 'blue'];
    }

    private function bestVisitWindow(array $target): ?string
    {
        if (($target['type'] ?? null) === 'party' && !empty($target['start_time'])) {
            return $this->formatBeforeSuggestion($target['start_time'], 60);
        }

        if (($target['type'] ?? null) === 'club' && !empty($target['open_time'])) {
            return $this->formatAfterSuggestion($target['open_time'], 60);
        }

        return null;
    }

    private function formatBeforeSuggestion(string $time, int $minutesBefore): string
    {
        $targetTime = Carbon::createFromFormat('H:i', substr($time, 0, 5), 'Asia/Seoul')->subMinutes($minutesBefore);

        return $targetTime->format('H:i') . ' 이전 입장 추천';
    }

    private function formatAfterSuggestion(string $time, int $minutesAfter): string
    {
        $targetTime = Carbon::createFromFormat('H:i', substr($time, 0, 5), 'Asia/Seoul')->addMinutes($minutesAfter);

        return $targetTime->format('H:i') . ' 이후 방문 추천';
    }

    private function fallbackSignal(array $target): array
    {
        return $this->buildSignal($target, collect());
    }

    private function key(string $type, int $id): string
    {
        return $type . ':' . $id;
    }
}
