<?php

namespace App\Services;

use App\Models\Club;
use App\Models\Favorite;
use App\Models\Inquiry;
use App\Models\NiteNotification;
use App\Models\Party;
use App\Models\RecentView;
use App\Models\TourRecommendation;
use App\Models\User;
use Illuminate\Support\Collection;

class RevisitHubService
{
    public function build(string $sessionId, ?User $user = null): array
    {
        $favoriteRecords = Favorite::query()
            ->forSession($sessionId)
            ->latest()
            ->limit(6)
            ->get();

        $recentViewRecords = RecentView::query()
            ->forSession($sessionId)
            ->orderByDesc('viewed_at')
            ->limit(6)
            ->get();

        $recentViews = $this->resolveRecords(
            $recentViewRecords,
            timestampField: 'viewed_at'
        );

        $favoriteItems = $this->resolveRecords(
            $favoriteRecords,
            timestampField: 'created_at'
        );

        $activeInquiries = collect();
        $openInquiryCount = 0;
        $pendingReplyCount = 0;

        if ($user) {
            $activeInquiries = Inquiry::query()
                ->where('user_id', $user->id)
                ->with('assignedMd', 'publicReplies')
                ->whereNotIn('status', ['consultation_completed', 'closed', 'hidden'])
                ->orderByRaw("
                    CASE status
                        WHEN 'pending' THEN 0
                        WHEN 'in_progress' THEN 1
                        WHEN 'answered' THEN 2
                        WHEN 'reservation_confirmed' THEN 3
                        ELSE 4
                    END
                ")
                ->orderByDesc('updated_at')
                ->limit(2)
                ->get();

            $openInquiryCount = Inquiry::query()
                ->where('user_id', $user->id)
                ->whereNotIn('status', ['consultation_completed', 'closed', 'hidden'])
                ->count();

            $pendingReplyCount = Inquiry::query()
                ->where('user_id', $user->id)
                ->whereIn('status', ['pending', 'in_progress'])
                ->count();
        }

        $unreadCount = NiteNotification::query()
            ->forViewer($sessionId, $user?->id)
            ->unread()
            ->count();

        return [
            'recentCount' => RecentView::query()->forSession($sessionId)->count(),
            'favCount' => Favorite::query()->forSession($sessionId)->count(),
            'unreadCount' => $unreadCount,
            'openInquiryCount' => $openInquiryCount,
            'pendingReplyCount' => $pendingReplyCount,
            'activeInquiries' => $activeInquiries,
            'topInquiry' => $activeInquiries->first(),
            'recentViews' => $recentViews->take(3)->values(),
            'continuePrimary' => $recentViews->first(),
            'favoriteItems' => $favoriteItems->take(3)->values(),
            'favoritePrimary' => $favoriteItems->first(),
            'hasSignals' => $openInquiryCount > 0
                || $unreadCount > 0
                || $recentViews->isNotEmpty()
                || $favoriteItems->isNotEmpty(),
        ];
    }

    private function resolveRecords(Collection $records, string $timestampField): Collection
    {
        if ($records->isEmpty()) {
            return collect();
        }

        $grouped = $records->groupBy('target_type');
        $clubs = $this->loadTargets(Club::class, $grouped->get('club', collect())->pluck('target_id')->all());
        $parties = $this->loadTargets(Party::class, $grouped->get('party', collect())->pluck('target_id')->all(), ['club']);
        $tours = $this->loadTargets(TourRecommendation::class, $grouped->get('tour', collect())->pluck('target_id')->all());

        return $records->map(function ($record) use ($timestampField, $clubs, $parties, $tours) {
            $target = match ($record->target_type) {
                'club' => $clubs->get($record->target_id),
                'party' => $parties->get($record->target_id),
                'tour' => $tours->get($record->target_id),
                default => null,
            };

            if (!$target) {
                return null;
            }

            return (object) [
                'type' => $record->target_type,
                'id' => $record->target_id,
                'target' => $target,
                'recorded_at' => $record->{$timestampField},
            ];
        })->filter()->values();
    }

    private function loadTargets(string $modelClass, array $ids, array $with = []): Collection
    {
        if (empty($ids)) {
            return collect();
        }

        return $modelClass::query()
            ->when(!empty($with), fn ($query) => $query->with($with))
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');
    }
}
