<?php

namespace App\Services;

use App\Models\Inquiry;
use App\Models\Review;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class ReviewSummaryService
{
    private array $tableExistsCache = [];

    public function buildForTarget(
        string $type,
        int $targetId,
        ?float $fallbackAverage = null,
        ?int $fallbackCount = null,
        ?string $fallbackSummary = null,
    ): array {
        if (!in_array($type, ['club', 'party'], true) || !$this->tableExists('reviews')) {
            return $this->emptySummary($fallbackAverage, $fallbackCount, $fallbackSummary);
        }

        $reviews = Review::query()
            ->forTarget($type, $targetId)
            ->visible()
            ->with('user')
            ->latest()
            ->get();

        $reviews = $this->attachVerificationFlags($reviews, $type, $targetId);
        $actualCount = $reviews->count();
        $actualAverage = $actualCount > 0
            ? round((float) $reviews->avg(fn (Review $review) => (int) ($review->rating ?? 0)), 1)
            : null;
        $displayCount = $actualCount > 0 ? $actualCount : max(0, (int) ($fallbackCount ?? 0));
        $displayAverage = $actualAverage ?? ($fallbackAverage !== null ? round((float) $fallbackAverage, 1) : null);
        $verifiedCount = $reviews
            ->filter(fn (Review $review) => (bool) $review->getAttribute('is_verified_review'))
            ->count();
        $topTags = $reviews
            ->pluck('tags')
            ->filter()
            ->flatten()
            ->filter(fn ($tag) => filled($tag))
            ->countBy()
            ->sortDesc()
            ->take(3)
            ->keys()
            ->values()
            ->all();

        return [
            'all_reviews' => $reviews,
            'recent_reviews' => $reviews->take(3)->values(),
            'display_average' => $displayAverage,
            'display_count' => $displayCount,
            'actual_count' => $actualCount,
            'verified_count' => $verifiedCount,
            'summary_text' => $this->buildSummaryText($fallbackSummary, $topTags, $actualCount),
            'top_tags' => $topTags,
            'count_caption' => $actualCount > 0 ? '실제 등록 후기 기준' : ($displayCount > 0 ? '누적 평점 요약 기준' : '첫 후기를 기다리는 중'),
            'verification_caption' => '검증 배지는 같은 장소에 문의 이력이 확인된 후기입니다.',
        ];
    }

    private function emptySummary(?float $fallbackAverage, ?int $fallbackCount, ?string $fallbackSummary): array
    {
        $displayCount = max(0, (int) ($fallbackCount ?? 0));

        return [
            'all_reviews' => collect(),
            'recent_reviews' => collect(),
            'display_average' => $fallbackAverage !== null ? round((float) $fallbackAverage, 1) : null,
            'display_count' => $displayCount,
            'actual_count' => 0,
            'verified_count' => 0,
            'summary_text' => $fallbackSummary,
            'top_tags' => [],
            'count_caption' => $displayCount > 0 ? '누적 평점 요약 기준' : '첫 후기를 기다리는 중',
            'verification_caption' => '검증 배지는 같은 장소에 문의 이력이 확인된 후기입니다.',
        ];
    }

    private function attachVerificationFlags(Collection $reviews, string $type, int $targetId): Collection
    {
        $verifiedUserIds = collect();

        if ($reviews->isNotEmpty() && $this->tableExists('inquiries')) {
            $userIds = $reviews->pluck('user_id')->filter()->unique()->values();

            if ($userIds->isNotEmpty()) {
                $verifiedUserIds = Inquiry::query()
                    ->where('target_type', $type)
                    ->where('target_id', $targetId)
                    ->whereIn('user_id', $userIds->all())
                    ->distinct()
                    ->pluck('user_id');
            }
        }

        return $reviews->each(function (Review $review) use ($verifiedUserIds) {
            $review->setAttribute(
                'is_verified_review',
                $review->user_id !== null && $verifiedUserIds->contains($review->user_id)
            );
        });
    }

    private function buildSummaryText(?string $fallbackSummary, array $topTags, int $actualCount): ?string
    {
        if (filled($fallbackSummary)) {
            return $fallbackSummary;
        }

        if ($actualCount === 0 || empty($topTags)) {
            return null;
        }

        if (count($topTags) === 1) {
            return $topTags[0] . ' 후기가 가장 많이 언급됩니다.';
        }

        return implode(', ', array_slice($topTags, 0, 2)) . ' 평가가 반복해서 언급됩니다.';
    }

    private function tableExists(string $table): bool
    {
        return $this->tableExistsCache[$table] ??= Schema::hasTable($table);
    }
}
