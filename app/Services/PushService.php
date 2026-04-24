<?php

namespace App\Services;

use App\Models\Club;
use App\Models\DeviceToken;
use App\Models\Favorite;
use App\Models\Inquiry;
use App\Models\NiteNotification;
use App\Models\NotificationSetting;
use App\Models\Party;
use App\Models\PushCampaign;
use App\Models\PushDeliveryLog;
use App\Models\RecentView;
use App\Models\UserPreference;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class PushService
{
    /**
     * 캠페인 발송 실행
     */
    public function sendCampaign(PushCampaign $campaign): void
    {
        $campaign->update(['status' => 'sending']);

        $recipients = $this->resolveRecipients($campaign);
        $campaign->update(['target_count' => $recipients->count()]);

        $sent = 0;
        $failed = 0;

        foreach ($recipients as $recipient) {
            $user = $recipient['user'];
            $log = PushDeliveryLog::create([
                'campaign_id' => $campaign->id,
                'user_id'     => $user->id,
                'status'      => 'pending',
            ]);

            try {
                $sessionId = $this->resolveSessionId($user);

                // 인앱 알림 생성
                NiteNotification::create([
                    'session_id' => $sessionId,
                    'user_id'  => $user->id,
                    'type'     => 'push_campaign',
                    'title'    => $recipient['title'],
                    'body'     => $recipient['body'],
                    'link'     => $this->appendCampaignTracking($recipient['link'], $campaign->id),
                    'data'     => array_merge(['campaign_id' => $campaign->id], $recipient['data'] ?? []),
                    'channel'  => 'in_app',
                    'is_read'  => false,
                    'sent_at'  => now(),
                ]);

                // FCM 발송 (향후 실제 연동 시 여기에 구현)
                // $this->sendFcm($user, $campaign);

                $log->update(['status' => 'sent', 'sent_at' => now()]);
                $sent++;
            } catch (\Throwable $e) {
                $log->update(['status' => 'failed']);
                $failed++;
                Log::error("Push delivery failed: campaign={$campaign->id}, user={$user->id}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $campaign->update([
            'status'       => $failed > 0 && $sent === 0 ? 'failed' : 'sent',
            'sent_count'   => $sent,
            'failed_count' => $failed,
            'sent_at'      => now(),
        ]);
    }

    private function resolveRecipients(PushCampaign $campaign): Collection
    {
        if ($campaign->target_type === 'custom') {
            return $this->resolveRetentionRecipients($campaign);
        }

        return $this->resolveTargetUsers($campaign)->map(fn (User $user) => [
            'user' => $user,
            'title' => $campaign->title,
            'body' => $campaign->body,
            'link' => $campaign->link,
            'data' => [],
        ]);
    }

    /**
     * 타겟 사용자 조회
     */
    private function resolveTargetUsers(PushCampaign $campaign)
    {
        $query = User::where('status', 'active');

        switch ($campaign->target_type) {
            case 'logged_in':
                $query->whereNotNull('last_login_at');
                break;
            case 'area':
                $areas = $campaign->target_query['areas'] ?? [];
                if ($areas) {
                    $query->whereHas('preference', function ($q) use ($areas) {
                        $q->whereJsonContains('preferred_areas', $areas[0]);
                        foreach (array_slice($areas, 1) as $area) {
                            $q->orWhereJsonContains('preferred_areas', $area);
                        }
                    });
                }
                break;
            case 'genre':
                $genres = $campaign->target_query['genres'] ?? [];
                if ($genres) {
                    $query->whereHas('preference', function ($q) use ($genres) {
                        $q->whereJsonContains('preferred_genres', $genres[0]);
                        foreach (array_slice($genres, 1) as $genre) {
                            $q->orWhereJsonContains('preferred_genres', $genre);
                        }
                    });
                }
                break;
        }

        // MD/관리자 제외 옵션
        if ($campaign->target_query['exclude_staff'] ?? false) {
            $query->whereNotIn('role', ['admin', 'super_admin', 'md']);
        }

        // 알림 수신 동의 사용자만
        $query->whereHas('notificationSetting', fn($q) => $q->where('enabled', true));

        return $query->get();
    }

    private function resolveRetentionRecipients(PushCampaign $campaign): Collection
    {
        $preset = data_get($campaign->target_query, 'retention_preset');
        $days = max(1, min(30, (int) data_get($campaign->target_query, 'retention_days', 7)));
        $users = $this->baseRetentionUsers($campaign)->get();

        if ($users->isEmpty() || !$preset) {
            return collect();
        }

        return match ($preset) {
            'recent_view_no_inquiry' => $this->resolveRecentViewRecipients($campaign, $users, $days),
            'favorite_no_inquiry' => $this->resolveFavoriteRecipients($campaign, $users, $days),
            'inquiry_reply_unread' => $this->resolveUnreadInquiryRecipients($campaign, $users, $days),
            default => collect(),
        };
    }

    private function baseRetentionUsers(PushCampaign $campaign)
    {
        $query = User::query()
            ->where('status', 'active')
            ->whereHas('notificationSetting', fn ($subQuery) => $subQuery->where('enabled', true));

        if ($campaign->target_query['exclude_staff'] ?? false) {
            $query->whereNotIn('role', ['admin', 'super_admin', 'md']);
        }

        return $query;
    }

    private function resolveRecentViewRecipients(PushCampaign $campaign, Collection $users, int $days): Collection
    {
        if (!Schema::hasTable('recent_views')) {
            return collect();
        }

        $records = RecentView::query()
            ->whereNotNull('user_id')
            ->whereIn('user_id', $users->pluck('id'))
            ->whereIn('target_type', ['club', 'party'])
            ->where('viewed_at', '>=', now()->subDays($days))
            ->orderByDesc('viewed_at')
            ->get();

        return $this->buildEntityRecipients($campaign, $users, $records, 'recent_view');
    }

    private function resolveFavoriteRecipients(PushCampaign $campaign, Collection $users, int $days): Collection
    {
        if (!Schema::hasTable('favorites')) {
            return collect();
        }

        $records = Favorite::query()
            ->whereNotNull('user_id')
            ->whereIn('user_id', $users->pluck('id'))
            ->whereIn('target_type', ['club', 'party'])
            ->where('created_at', '>=', now()->subDays($days))
            ->latest()
            ->get();

        return $this->buildEntityRecipients($campaign, $users, $records, 'favorite');
    }

    private function buildEntityRecipients(PushCampaign $campaign, Collection $users, Collection $records, string $source): Collection
    {
        if ($records->isEmpty()) {
            return collect();
        }

        $targets = $this->loadRetentionTargets($records);

        return $records
            ->groupBy('user_id')
            ->map(function (Collection $userRecords, int $userId) use ($campaign, $users, $targets, $source) {
                $user = $users->firstWhere('id', $userId);

                if (!$user) {
                    return null;
                }

                foreach ($userRecords as $record) {
                    $target = data_get($targets, "{$record->target_type}.{$record->target_id}");

                    if (!$target || $this->hasInquiryForTarget($userId, $record->target_type, $record->target_id)) {
                        continue;
                    }

                    return [
                        'user' => $user,
                        'title' => $campaign->title,
                        'body' => $campaign->body,
                        'link' => $this->entityLink($record->target_type, $target->id),
                        'data' => [
                            'retention_preset' => data_get($campaign->target_query, 'retention_preset'),
                            'retention_source' => $source,
                            'target_type' => $record->target_type,
                            'target_id' => $target->id,
                        ],
                    ];
                }

                return null;
            })
            ->filter()
            ->values();
    }

    private function resolveUnreadInquiryRecipients(PushCampaign $campaign, Collection $users, int $days): Collection
    {
        if (!Schema::hasTable('nite_notifications')) {
            return collect();
        }

        return NiteNotification::query()
            ->whereIn('user_id', $users->pluck('id'))
            ->where('type', 'inquiry_update')
            ->where('is_read', false)
            ->where('created_at', '>=', now()->subDays($days))
            ->latest('created_at')
            ->get()
            ->filter(function (NiteNotification $notification) {
                $title = (string) $notification->title;
                $body = (string) $notification->body;

                return str_contains($title, '답변') || str_contains($body, '답변');
            })
            ->groupBy('user_id')
            ->map(function (Collection $notifications, int $userId) use ($campaign, $users) {
                $user = $users->firstWhere('id', $userId);
                $notification = $notifications->first();

                if (!$user || !$notification) {
                    return null;
                }

                return [
                    'user' => $user,
                    'title' => $campaign->title,
                    'body' => $campaign->body,
                    'link' => $notification->link ?: $campaign->link,
                    'data' => [
                        'retention_preset' => data_get($campaign->target_query, 'retention_preset'),
                        'retention_source' => 'inquiry_notification',
                        'inquiry_id' => data_get($notification->data, 'inquiry_id'),
                        'source_notification_id' => $notification->id,
                    ],
                ];
            })
            ->filter()
            ->values();
    }

    private function loadRetentionTargets(Collection $records): array
    {
        $clubs = Club::query()
            ->whereIn('id', $records->where('target_type', 'club')->pluck('target_id')->unique()->all())
            ->get()
            ->keyBy('id');

        $parties = Party::query()
            ->whereIn('id', $records->where('target_type', 'party')->pluck('target_id')->unique()->all())
            ->get()
            ->keyBy('id');

        return [
            'club' => $clubs,
            'party' => $parties,
        ];
    }

    private function hasInquiryForTarget(int $userId, string $targetType, int $targetId): bool
    {
        return Inquiry::query()
            ->where('user_id', $userId)
            ->where('target_type', $targetType)
            ->where('target_id', $targetId)
            ->exists();
    }

    private function entityLink(string $targetType, int $targetId): ?string
    {
        return match ($targetType) {
            'club' => "/clubs/{$targetId}",
            'party' => "/parties/{$targetId}",
            default => null,
        };
    }

    private function appendCampaignTracking(?string $link, int $campaignId): ?string
    {
        if (!$link) {
            return null;
        }

        $fragment = '';

        if (str_contains($link, '#')) {
            [$link, $fragment] = explode('#', $link, 2);
            $fragment = '#' . $fragment;
        }

        $separator = str_contains($link, '?') ? '&' : '?';

        return $link . $separator . 'utm_campaign=' . $campaignId . $fragment;
    }

    private function resolveSessionId(User $user): string
    {
        $sessionId = $user->notificationSetting?->session_id
            ?? NotificationSetting::where('user_id', $user->id)->value('session_id')
            ?? UserPreference::where('user_id', $user->id)->value('session_id')
            ?? NiteNotification::where('user_id', $user->id)->whereNotNull('session_id')->latest('id')->value('session_id');

        return $sessionId ?: 'user:' . $user->id;
    }
}
