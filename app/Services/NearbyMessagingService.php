<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageReport;
use App\Models\NearbyVisibilitySetting;
use App\Models\User;
use App\Models\UserBlock;
use App\Models\UserLocationStatus;
use App\Models\VenueCheckin;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class NearbyMessagingService
{
    public function visibilityStatusFor(User $user): array
    {
        $setting = $this->ensureVisibilitySetting($user);
        $status = $this->ensureLocationStatus($user);

        return [
            'settings' => $setting,
            'location_status' => $status,
            'has_active_location' => $status->expires_at?->isFuture() ?? false,
        ];
    }

    public function updateVisibility(User $user, array $attributes): NearbyVisibilitySetting
    {
        $setting = $this->ensureVisibilitySetting($user);

        $isEnabled = (bool) ($attributes['is_enabled'] ?? $setting->is_enabled);
        $shareScope = $this->normalizeShareScope($attributes['share_scope'] ?? $setting->share_scope);
        $isVisible = $isEnabled
            && $shareScope !== 'off'
            && (bool) ($attributes['is_visible'] ?? $setting->is_visible);

        $setting->fill([
            'is_enabled' => $isEnabled,
            'is_visible' => $isVisible,
            'share_scope' => $isEnabled ? $shareScope : 'off',
            'hide_exact_venue' => (bool) ($attributes['hide_exact_venue'] ?? $setting->hide_exact_venue ?? true),
            'foreign_mode' => (bool) ($attributes['foreign_mode'] ?? $setting->foreign_mode ?? false),
            'preferred_languages' => $this->normalizeStringList($attributes['preferred_languages'] ?? $setting->preferred_languages ?? []),
            'preferred_interests' => $this->normalizeStringList($attributes['preferred_interests'] ?? $setting->preferred_interests ?? []),
            'preferred_intentions' => $this->normalizeStringList($attributes['preferred_intentions'] ?? $setting->preferred_intentions ?? []),
            'profile_gender' => $this->nullableString($attributes['profile_gender'] ?? $setting->profile_gender),
            'profile_age_band' => $this->nullableString($attributes['profile_age_band'] ?? $setting->profile_age_band),
            'auto_hide_after_minutes' => max(1, (int) ($attributes['auto_hide_after_minutes'] ?? $setting->auto_hide_after_minutes ?? config('nearby-messaging.location_ttl_minutes', 10))),
        ]);
        $setting->save();

        if (!$setting->is_enabled) {
            UserLocationStatus::where('user_id', $user->id)->update([
                'is_location_enabled' => false,
                'is_visible_nearby' => false,
                'expires_at' => now(),
                'venue_type' => null,
                'venue_id' => null,
                'updated_at' => now(),
            ]);

            VenueCheckin::where('user_id', $user->id)
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    'updated_at' => now(),
                ]);
        }

        return $setting->fresh();
    }

    public function updateLocation(User $user, array $attributes, ?string $sessionId = null): UserLocationStatus
    {
        $setting = $this->ensureVisibilitySetting($user);

        if (!$setting->is_enabled) {
            throw ValidationException::withMessages([
                'location' => '위치 공유를 먼저 켜야 합니다.',
            ]);
        }

        $lat = (float) $attributes['lat'];
        $lng = (float) $attributes['lng'];
        $ttlMinutes = max(1, (int) ($setting->auto_hide_after_minutes ?: config('nearby-messaging.location_ttl_minutes', 10)));

        $status = $this->ensureLocationStatus($user);
        $status->fill([
            'session_id' => $sessionId,
            'last_lat' => $lat,
            'last_lng' => $lng,
            'last_accuracy_m' => isset($attributes['accuracy_m']) ? (float) $attributes['accuracy_m'] : null,
            'last_area' => GeoService::nearestArea($lat, $lng),
            'last_geohash' => $this->coarseLocationKey($lat, $lng),
            'venue_type' => $attributes['venue_type'] ?? $status->venue_type,
            'venue_id' => $attributes['venue_id'] ?? $status->venue_id,
            'is_location_enabled' => true,
            'is_visible_nearby' => $setting->is_visible,
            'seen_at' => now(),
            'expires_at' => now()->addMinutes($ttlMinutes),
        ]);
        $status->save();

        if (!empty($attributes['venue_type']) && !empty($attributes['venue_id'])) {
            $this->upsertVenueCheckin(
                $user,
                (string) $attributes['venue_type'],
                (int) $attributes['venue_id'],
                (string) ($attributes['source'] ?? 'location_update')
            );
        }

        return $status->fresh();
    }

    public function upsertVenueCheckin(User $user, string $venueType, int $venueId, string $source = 'manual'): VenueCheckin
    {
        if (!in_array($venueType, ['club', 'party'], true)) {
            throw ValidationException::withMessages([
                'venue_type' => '지원하지 않는 장소 타입입니다.',
            ]);
        }

        VenueCheckin::where('user_id', $user->id)
            ->where('is_active', true)
            ->update([
                'is_active' => false,
                'updated_at' => now(),
            ]);

        $checkin = VenueCheckin::create([
            'user_id' => $user->id,
            'venue_type' => $venueType,
            'venue_id' => $venueId,
            'source' => $source,
            'is_active' => true,
            'checked_in_at' => now(),
            'expires_at' => now()->addMinutes((int) config('nearby-messaging.same_venue_ttl_minutes', 180)),
        ]);

        UserLocationStatus::where('user_id', $user->id)->update([
            'venue_type' => $venueType,
            'venue_id' => $venueId,
            'updated_at' => now(),
        ]);

        return $checkin;
    }

    public function getNearbyUsers(User $viewer, array $filters = []): array
    {
        $viewerStatus = $this->ensureLocationStatus($viewer);
        $viewerSetting = $this->ensureVisibilitySetting($viewer);

        if (!$viewerSetting->is_enabled || !$viewerStatus->expires_at?->isFuture()) {
            throw ValidationException::withMessages([
                'location' => '활성 위치 정보가 없습니다. 위치 공유를 켜고 현재 위치를 업데이트해 주세요.',
            ]);
        }

        $blockedIds = UserBlock::query()
            ->where(function ($query) use ($viewer) {
                $query->where('blocker_id', $viewer->id)
                    ->orWhere('blocked_id', $viewer->id);
            })
            ->get()
            ->map(function (UserBlock $block) use ($viewer) {
                return $block->blocker_id === $viewer->id ? $block->blocked_id : $block->blocker_id;
            })
            ->all();

        $rawStatuses = UserLocationStatus::query()
            ->with([
                'user' => fn ($query) => $query->select('id', 'name', 'nickname', 'status'),
                'user.nearbyVisibilitySetting',
                'user.profileImages' => fn ($query) => $query->current(),
            ])
            ->active()
            ->where('is_visible_nearby', true)
            ->where('user_id', '!=', $viewer->id)
            ->when(!empty($blockedIds), fn ($query) => $query->whereNotIn('user_id', $blockedIds))
            ->orderByDesc('seen_at')
            ->limit(max((int) config('nearby-messaging.list_limit', 40) * 4, 120))
            ->get();

        $languages = $this->normalizeStringList($filters['languages'] ?? []);
        $interests = $this->normalizeStringList($filters['interests'] ?? []);
        $intentions = $this->normalizeStringList($filters['intentions'] ?? []);
        $foreignOnly = (bool) ($filters['foreign_only'] ?? false);

        $users = $rawStatuses
            ->map(function (UserLocationStatus $status) use ($viewerStatus) {
                $distanceKm = GeoService::haversineDistance(
                    (float) $viewerStatus->last_lat,
                    (float) $viewerStatus->last_lng,
                    (float) $status->last_lat,
                    (float) $status->last_lng
                );

                $distanceM = (int) round($distanceKm * 1000);
                $bucket = $this->resolveDistanceBucket($viewerStatus, $status, $distanceM);

                if (!$bucket) {
                    return null;
                }

                $user = $status->user;
                $setting = $user?->nearbyVisibilitySetting;

                return [
                    'sort_score' => $this->distanceSortScore($bucket['key']),
                    'distance_m' => $distanceM,
                    'user_id' => $user->id,
                    'nickname' => $user->nickname ?: $user->name,
                    'profile_image_url' => $user->profileImages->first()?->thumb_url ?? asset('images/default-profile.svg'),
                    'distance_bucket' => $bucket['key'],
                    'distance_label' => $bucket['label'],
                    'area' => $status->last_area,
                    'foreign_mode' => (bool) ($setting?->foreign_mode ?? false),
                    'languages' => $setting?->preferred_languages ?? [],
                    'interests' => $setting?->preferred_interests ?? [],
                    'intentions' => $setting?->preferred_intentions ?? [],
                    'profile_gender' => $setting?->profile_gender,
                    'profile_age_band' => $setting?->profile_age_band,
                    'venue_type' => $status->venue_type,
                    'venue_id' => $status->venue_id,
                    'last_seen_at' => optional($status->seen_at)->toIso8601String(),
                    'can_message' => true,
                ];
            })
            ->filter()
            ->filter(function (array $item) use ($languages, $interests, $intentions, $foreignOnly) {
                if ($foreignOnly && !$item['foreign_mode']) {
                    return false;
                }

                if (!$this->matchesAnyFilter($languages, $item['languages'])) {
                    return false;
                }

                if (!$this->matchesAnyFilter($interests, $item['interests'])) {
                    return false;
                }

                if (!$this->matchesAnyFilter($intentions, $item['intentions'])) {
                    return false;
                }

                return true;
            })
            ->sortBy(fn (array $item) => sprintf('%02d-%06d-%010d', $item['sort_score'], $item['distance_m'], $item['user_id']))
            ->take((int) config('nearby-messaging.list_limit', 40))
            ->values()
            ->map(function (array $item) {
                unset($item['sort_score'], $item['distance_m']);
                return $item;
            });

        return [
            'data' => $users,
            'meta' => [
                'count' => $users->count(),
                'sharing_enabled' => $viewerSetting->is_enabled,
                'is_visible' => $viewerSetting->is_visible,
                'share_scope' => $viewerSetting->share_scope,
                'last_area' => $viewerStatus->last_area,
                'last_seen_at' => optional($viewerStatus->seen_at)->toIso8601String(),
            ],
        ];
    }

    public function listConversations(User $user): array
    {
        $conversations = Conversation::query()
            ->with([
                'userOne' => fn ($query) => $query->select('id', 'name', 'nickname', 'status'),
                'userTwo' => fn ($query) => $query->select('id', 'name', 'nickname', 'status'),
                'userOne.profileImages' => fn ($query) => $query->current(),
                'userTwo.profileImages' => fn ($query) => $query->current(),
            ])
            ->forUser($user->id)
            ->orderByDesc(DB::raw('coalesce(last_message_at, created_at)'))
            ->get();

        $staleBefore = now()->subMinutes((int) config('nearby-messaging.message_ttl_minutes', 30));
        foreach ($conversations as $conversation) {
            if ($conversation->last_message_at && $conversation->last_message_at->lte($staleBefore)) {
                $this->refreshConversationState($conversation);
            }
        }

        $conversations = Conversation::query()
            ->with([
                'userOne' => fn ($query) => $query->select('id', 'name', 'nickname', 'status'),
                'userTwo' => fn ($query) => $query->select('id', 'name', 'nickname', 'status'),
                'userOne.profileImages' => fn ($query) => $query->current(),
                'userTwo.profileImages' => fn ($query) => $query->current(),
            ])
            ->whereIn('id', $conversations->pluck('id'))
            ->orderByDesc(DB::raw('coalesce(last_message_at, created_at)'))
            ->get();

        $unreadCounts = Message::query()
            ->alive()
            ->where('recipient_id', $user->id)
            ->whereNull('read_at')
            ->whereIn('conversation_id', $conversations->pluck('id'))
            ->selectRaw('conversation_id, count(*) as unread_count')
            ->groupBy('conversation_id')
            ->pluck('unread_count', 'conversation_id');

        return [
            'data' => $conversations->map(function (Conversation $conversation) use ($user, $unreadCounts) {
                $otherUser = $this->otherParticipant($conversation, $user->id);

                return [
                    'id' => $conversation->id,
                    'other_user' => $this->serializeUserSummary($otherUser),
                    'last_message_preview' => $conversation->last_message_preview,
                    'last_message_at' => optional($conversation->last_message_at)->toIso8601String(),
                    'unread_count' => (int) ($unreadCounts[$conversation->id] ?? 0),
                    'blocked_at' => optional($conversation->blocked_at)->toIso8601String(),
                    'created_at' => optional($conversation->created_at)->toIso8601String(),
                ];
            })->values(),
        ];
    }

    public function startConversation(User $user, User $otherUser): Conversation
    {
        if ($user->id === $otherUser->id) {
            throw ValidationException::withMessages([
                'recipient_user_id' => '자기 자신에게는 메시지를 보낼 수 없습니다.',
            ]);
        }

        if (!$otherUser->isActive() || !$user->isActive()) {
            throw ValidationException::withMessages([
                'recipient_user_id' => '현재 대화를 시작할 수 없는 사용자입니다.',
            ]);
        }

        if (!$user->canWrite()) {
            throw ValidationException::withMessages([
                'message' => '현재는 메시지 전송이 제한되어 있습니다.',
            ]);
        }

        if ($this->isBlockedBetween($user->id, $otherUser->id)) {
            throw ValidationException::withMessages([
                'recipient_user_id' => '차단 상태인 사용자와는 대화를 시작할 수 없습니다.',
            ]);
        }

        [$userOneId, $userTwoId] = $user->id < $otherUser->id
            ? [$user->id, $otherUser->id]
            : [$otherUser->id, $user->id];

        $conversation = Conversation::firstOrCreate(
            [
                'user_one_id' => $userOneId,
                'user_two_id' => $userTwoId,
            ],
            [
                'starter_id' => $user->id,
            ]
        );

        if (!$conversation->wasRecentlyCreated) {
            return $conversation;
        }

        $recentStarts = Conversation::query()
            ->where('starter_id', $user->id)
            ->where('created_at', '>=', now()->subMinutes(10))
            ->count();

        if ($recentStarts > (int) config('nearby-messaging.new_conversation_limit_per_10m', 3)) {
            $conversation->delete();

            throw ValidationException::withMessages([
                'recipient_user_id' => '새 대화 시작이 너무 많습니다. 잠시 후 다시 시도해 주세요.',
            ]);
        }

        return $conversation;
    }

    public function getConversation(User $user, Conversation $conversation): array
    {
        $this->assertParticipant($user, $conversation);
        $this->refreshConversationState($conversation);
        $conversation = $conversation->fresh();
        $conversation->load([
            'userOne.profileImages' => fn ($query) => $query->current(),
            'userTwo.profileImages' => fn ($query) => $query->current(),
        ]);

        return [
            'conversation' => [
                'id' => $conversation->id,
                'other_user' => $this->serializeUserSummary($this->otherParticipant($conversation, $user->id)),
                'last_message_preview' => $conversation->last_message_preview,
                'last_message_at' => optional($conversation->last_message_at)->toIso8601String(),
                'blocked_at' => optional($conversation->blocked_at)->toIso8601String(),
                'created_at' => optional($conversation->created_at)->toIso8601String(),
            ],
            'messages' => $this->getMessages($user, $conversation)['data'],
        ];
    }

    public function getMessages(User $user, Conversation $conversation, ?int $afterId = null, int $limit = 50): array
    {
        $this->assertParticipant($user, $conversation);

        $query = Message::query()
            ->alive()
            ->where('conversation_id', $conversation->id);

        if ($afterId) {
            $messages = $query->where('id', '>', $afterId)
                ->orderBy('id')
                ->limit($limit)
                ->get();
        } else {
            $messages = $query->latest('id')
                ->limit($limit)
                ->get()
                ->reverse()
                ->values();
        }

        return [
            'data' => $messages->map(function (Message $message) use ($user) {
                return [
                    'id' => $message->id,
                    'body' => $message->body,
                    'is_mine' => $message->sender_id === $user->id,
                    'sender_id' => $message->sender_id,
                    'recipient_id' => $message->recipient_id,
                    'read_at' => optional($message->read_at)->toIso8601String(),
                    'created_at' => optional($message->created_at)->toIso8601String(),
                    'expires_at' => optional($message->expires_at)->toIso8601String(),
                ];
            })->values(),
        ];
    }

    public function sendMessage(User $user, Conversation $conversation, string $body): Message
    {
        $this->assertParticipant($user, $conversation);

        if (!$user->canWrite()) {
            throw ValidationException::withMessages([
                'body' => '현재는 메시지 전송이 제한되어 있습니다.',
            ]);
        }

        $recipientId = $conversation->otherUserId($user->id);
        if (!$recipientId) {
            throw new AuthorizationException('대화 참여자만 메시지를 보낼 수 있습니다.');
        }

        if ($this->isBlockedBetween($user->id, $recipientId)) {
            throw ValidationException::withMessages([
                'body' => '차단 상태인 사용자와는 메시지를 주고받을 수 없습니다.',
            ]);
        }

        $recentCount = Message::query()
            ->where('sender_id', $user->id)
            ->where('created_at', '>=', now()->subSeconds((int) config('nearby-messaging.message_limit_window_seconds', 30)))
            ->count();

        if ($recentCount >= (int) config('nearby-messaging.message_limit_per_minute', 5)) {
            throw ValidationException::withMessages([
                'body' => '메시지를 너무 빠르게 보내고 있습니다. 잠시 후 다시 시도해 주세요.',
            ]);
        }

        $moderationResult = ForbiddenWordFilter::check($body);
        if (!$moderationResult['passed'] && $moderationResult['action'] !== 'mask') {
            throw ValidationException::withMessages([
                'body' => '전송할 수 없는 표현이 포함되어 있습니다.',
            ]);
        }

        if ($moderationResult['action'] === 'mask') {
            $body = ForbiddenWordFilter::mask($body);
        }

        return DB::transaction(function () use ($conversation, $user, $recipientId, $body) {
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $user->id,
                'recipient_id' => $recipientId,
                'body' => trim($body),
                'expires_at' => now()->addMinutes((int) config('nearby-messaging.message_ttl_minutes', 30)),
            ]);

            $readColumn = $conversation->user_one_id === $user->id ? 'last_read_user_one_at' : 'last_read_user_two_at';
            $leftColumn = $conversation->user_one_id === $user->id ? 'left_by_user_one_at' : 'left_by_user_two_at';

            $conversation->update([
                'last_message_id' => $message->id,
                'last_message_preview' => Str::limit($message->body, (int) config('nearby-messaging.message_preview_length', 80)),
                'last_message_at' => $message->created_at,
                $readColumn => now(),
                $leftColumn => null,
            ]);

            return $message->fresh();
        });
    }

    public function markConversationRead(User $user, Conversation $conversation): int
    {
        $this->assertParticipant($user, $conversation);

        $updated = Message::query()
            ->alive()
            ->where('conversation_id', $conversation->id)
            ->where('recipient_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $readColumn = $conversation->user_one_id === $user->id ? 'last_read_user_one_at' : 'last_read_user_two_at';
        $conversation->update([$readColumn => now()]);

        return $updated;
    }

    public function leaveConversation(User $user, Conversation $conversation): void
    {
        $this->assertParticipant($user, $conversation);

        $column = $conversation->user_one_id === $user->id ? 'left_by_user_one_at' : 'left_by_user_two_at';
        $conversation->update([$column => now()]);
    }

    public function reportMessage(User $user, Message $message, array $attributes): MessageReport
    {
        $conversation = $message->conversation;
        $this->assertParticipant($user, $conversation);

        $report = MessageReport::updateOrCreate(
            [
                'message_id' => $message->id,
                'reporter_id' => $user->id,
            ],
            [
                'conversation_id' => $message->conversation_id,
                'reported_user_id' => $message->sender_id,
                'reason' => $attributes['reason'],
                'detail' => $this->nullableString($attributes['detail'] ?? null),
                'snapshot_body' => $message->body,
                'status' => 'pending',
            ]
        );

        DB::table('moderation_logs')->insert([
            'target_type' => 'message',
            'target_id' => $message->id,
            'action' => 'report',
            'trigger_type' => 'manual',
            'reason' => $attributes['reason'],
            'created_by' => $user->id,
            'created_at' => now(),
        ]);

        return $report;
    }

    public function blockUser(User $user, User $otherUser, ?string $reason = null): UserBlock
    {
        if ($user->id === $otherUser->id) {
            throw ValidationException::withMessages([
                'user' => '자기 자신은 차단할 수 없습니다.',
            ]);
        }

        $block = UserBlock::updateOrCreate(
            [
                'blocker_id' => $user->id,
                'blocked_id' => $otherUser->id,
            ],
            [
                'reason' => $this->nullableString($reason),
            ]
        );

        Conversation::query()
            ->forUser($user->id)
            ->where(function ($query) use ($otherUser) {
                $query->where('user_one_id', $otherUser->id)
                    ->orWhere('user_two_id', $otherUser->id);
            })
            ->update([
                'blocked_at' => now(),
                'updated_at' => now(),
            ]);

        return $block;
    }

    public function unblockUser(User $user, User $otherUser): void
    {
        UserBlock::query()
            ->where('blocker_id', $user->id)
            ->where('blocked_id', $otherUser->id)
            ->delete();
    }

    public function purgeExpiredMessages(?Carbon $now = null): int
    {
        $now = $now ?: now();

        $baseQuery = Message::query()->where('expires_at', '<=', $now);
        $conversationIds = (clone $baseQuery)->distinct()->pluck('conversation_id');
        $deleted = (clone $baseQuery)->delete();

        foreach ($conversationIds as $conversationId) {
            $conversation = Conversation::find($conversationId);
            if ($conversation) {
                $this->refreshConversationState($conversation);
            }
        }

        return $deleted;
    }

    public function expireStalePresence(?Carbon $now = null): array
    {
        $now = $now ?: now();

        $statusCount = UserLocationStatus::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $now)
            ->where(function ($query) {
                $query->where('is_location_enabled', true)
                    ->orWhere('is_visible_nearby', true)
                    ->orWhereNotNull('venue_type');
            })
            ->update([
                'is_location_enabled' => false,
                'is_visible_nearby' => false,
                'venue_type' => null,
                'venue_id' => null,
                'updated_at' => $now,
            ]);

        $checkinCount = VenueCheckin::query()
            ->where('is_active', true)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $now)
            ->update([
                'is_active' => false,
                'updated_at' => $now,
            ]);

        return [
            'location_statuses' => $statusCount,
            'venue_checkins' => $checkinCount,
        ];
    }

    public function refreshConversationState(Conversation $conversation): void
    {
        $latestMessage = Message::query()
            ->alive()
            ->where('conversation_id', $conversation->id)
            ->latest('id')
            ->first();

        $conversation->update([
            'last_message_id' => $latestMessage?->id,
            'last_message_preview' => $latestMessage
                ? Str::limit($latestMessage->body, (int) config('nearby-messaging.message_preview_length', 80))
                : null,
            'last_message_at' => $latestMessage?->created_at,
        ]);
    }

    private function ensureVisibilitySetting(User $user): NearbyVisibilitySetting
    {
        return NearbyVisibilitySetting::firstOrCreate(
            ['user_id' => $user->id],
            [
                'is_enabled' => false,
                'is_visible' => false,
                'share_scope' => 'off',
                'hide_exact_venue' => true,
                'auto_hide_after_minutes' => (int) config('nearby-messaging.location_ttl_minutes', 10),
            ]
        );
    }

    private function ensureLocationStatus(User $user): UserLocationStatus
    {
        return UserLocationStatus::firstOrCreate(
            ['user_id' => $user->id],
            [
                'is_location_enabled' => false,
                'is_visible_nearby' => false,
            ]
        );
    }

    private function assertParticipant(User $user, Conversation $conversation): void
    {
        if (!$conversation->hasParticipant($user->id)) {
            throw new AuthorizationException('대화 참여자만 접근할 수 있습니다.');
        }
    }

    private function otherParticipant(Conversation $conversation, int $userId): ?User
    {
        if ($conversation->user_one_id === $userId) {
            return $conversation->userTwo;
        }

        if ($conversation->user_two_id === $userId) {
            return $conversation->userOne;
        }

        return null;
    }

    private function serializeUserSummary(?User $user): ?array
    {
        if (!$user) {
            return null;
        }

        return [
            'id' => $user->id,
            'nickname' => $user->nickname ?: $user->name,
            'profile_image_url' => $user->profileImages->first()?->thumb_url ?? asset('images/default-profile.svg'),
            'is_active' => $user->isActive(),
        ];
    }

    private function resolveDistanceBucket(UserLocationStatus $viewerStatus, UserLocationStatus $candidateStatus, int $distanceM): ?array
    {
        $sameVenue = $viewerStatus->venue_type
            && $viewerStatus->venue_id
            && $viewerStatus->venue_type === $candidateStatus->venue_type
            && (int) $viewerStatus->venue_id === (int) $candidateStatus->venue_id;

        if ($sameVenue && $candidateStatus->venue_type === 'club') {
            return ['key' => 'same_club_active', 'label' => '같은 클럽 입장 중'];
        }

        if ($sameVenue) {
            return ['key' => 'same_venue', 'label' => '같은 장소에 있어요'];
        }

        if ($distanceM <= 100) {
            return ['key' => 'within_100m', 'label' => '내 근처 100m 이내'];
        }

        if ($distanceM <= min((int) config('nearby-messaging.max_radius_m', 300), 300)) {
            return ['key' => 'within_300m', 'label' => '내 근처 300m 이내'];
        }

        return null;
    }

    private function distanceSortScore(string $bucket): int
    {
        return match ($bucket) {
            'same_venue', 'same_club_active' => 0,
            'within_100m' => 1,
            default => 2,
        };
    }

    private function matchesAnyFilter(array $needles, array $haystack): bool
    {
        if (empty($needles)) {
            return true;
        }

        return count(array_intersect($needles, $haystack)) > 0;
    }

    private function isBlockedBetween(int $userId, int $otherUserId): bool
    {
        return UserBlock::between($userId, $otherUserId)->exists();
    }

    private function normalizeShareScope(?string $value): string
    {
        return in_array($value, ['off', 'venue_only', 'nearby'], true) ? $value : 'off';
    }

    private function normalizeStringList(array $values): array
    {
        return collect($values)
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function coarseLocationKey(float $lat, float $lng): string
    {
        return sprintf('%0.3f:%0.3f', round($lat, 3), round($lng, 3));
    }
}
