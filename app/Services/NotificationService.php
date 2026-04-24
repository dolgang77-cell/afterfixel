<?php

namespace App\Services;

use App\Models\Club;
use App\Models\FavoriteParty;
use App\Models\NiteNotification;
use App\Models\NotificationSetting;
use App\Models\Party;
use App\Models\SavedFilter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * 찜한 파티 시작 전 리마인더 발송
     */
    public function sendPartyReminders(): int
    {
        $count = 0;
        $settings = NotificationSetting::where('enabled', true)->get();

        foreach ($settings as $setting) {
            $remindMinutes = $setting->remind_before ?? ['1440', '180'];

            foreach ($remindMinutes as $minutes) {
                $targetTime = now()->addMinutes((int) $minutes);
                $targetDate = $targetTime->toDateString();
                $targetHour = $targetTime->format('H:i');

                // 이 세션의 찜한 파티 중 해당 시간에 시작하는 파티
                $favorites = FavoriteParty::where('session_id', $setting->session_id)
                    ->whereHas('party', function ($q) use ($targetDate, $targetHour) {
                        $q->where('event_date', $targetDate)
                          ->where('start_time', $targetHour)
                          ->where('status', '!=', 'cancelled');
                    })
                    ->with('party.club')
                    ->get();

                foreach ($favorites as $fav) {
                    $party = $fav->party;

                    // 중복 발송 방지
                    $exists = NiteNotification::where('session_id', $setting->session_id)
                        ->where('type', NiteNotification::TYPE_PARTY_REMINDER)
                        ->whereJsonContains('data->party_id', $party->id)
                        ->whereJsonContains('data->remind_minutes', (int) $minutes)
                        ->exists();

                    if ($exists) continue;

                    $label = $this->minutesToLabel((int) $minutes);

                    $this->create(
                        sessionId: $setting->session_id,
                        userId: $setting->user_id,
                        type: NiteNotification::TYPE_PARTY_REMINDER,
                        title: "{$party->event_card_label} · {$party->name} {$label} 시작",
                        body: ($party->club?->name ?? '') . "에서 {$party->start_time}에 시작하는 " . $this->partyLabelForMessage($party) . "입니다.",
                        link: "/parties/{$party->id}",
                        data: [
                            'party_id'       => $party->id,
                            'remind_minutes' => (int) $minutes,
                            'event_card_type' => $party->event_card_type,
                            'event_card_label' => $party->event_card_label,
                            'event_card_notice' => $party->event_card_notice,
                        ],
                    );
                    $count++;
                }
            }
        }

        Log::info("PartyReminder: {$count}건 발송");
        return $count;
    }

    /**
     * 관심사 기반 신규 파티 매칭 알림
     */
    public function sendNewPartyAlerts(Party $party): int
    {
        $count = 0;

        $settings = NotificationSetting::where('enabled', true)
            ->where('new_party_alert', true)
            ->get();

        foreach ($settings as $setting) {
            $areas  = $setting->preferred_areas ?? [];
            $genres = $setting->preferred_genres ?? [];

            // 관심사가 비어있으면 스킵
            if (empty($areas) && empty($genres)) continue;

            $clubArea  = $party->club?->area;
            $partyGenre = $party->genre;

            $areaMatch  = empty($areas) || in_array($clubArea, $areas);
            $genreMatch = empty($genres) || in_array($partyGenre, $genres);

            if (!$areaMatch && !$genreMatch) continue;

            // 중복 방지
            $exists = NiteNotification::where('session_id', $setting->session_id)
                ->where('type', NiteNotification::TYPE_NEW_PARTY_MATCH)
                ->whereJsonContains('data->party_id', $party->id)
                ->exists();

            if ($exists) continue;

            $matchReasons = [];
            if ($areaMatch && $clubArea) $matchReasons[] = $clubArea;
            if ($genreMatch && $partyGenre) $matchReasons[] = $partyGenre;

            $this->create(
                sessionId: $setting->session_id,
                userId: $setting->user_id,
                type: NiteNotification::TYPE_NEW_PARTY_MATCH,
                title: "새 파티: {$party->name} · {$party->event_card_label}",
                body: implode(' · ', $matchReasons) . ' 관심사에 맞는 ' . $this->partyLabelForMessage($party) . '가 등록되었습니다.',
                link: "/parties/{$party->id}",
                data: [
                    'party_id' => $party->id,
                    'match_reasons' => $matchReasons,
                    'event_card_type' => $party->event_card_type,
                    'event_card_label' => $party->event_card_label,
                    'event_card_notice' => $party->event_card_notice,
                ],
            );
            $count++;
        }

        $count += $this->sendSavedFilterAlertsForParty($party);

        Log::info("NewPartyAlert: {$party->name} → {$count}건 발송");
        return $count;
    }

    public function sendNewClubAlerts(Club $club): int
    {
        $count = $this->sendSavedFilterAlertsForClub($club);

        Log::info("NewClubAlert: {$club->name} → {$count}건 발송");

        return $count;
    }

    /**
     * 오늘 밤 추천 알림 (금/토 저녁)
     */
    public function sendTonightRecommendations(): int
    {
        $count = 0;
        $today = today();

        // 오늘의 파티
        $todayParties = Party::where('event_date', $today)
            ->where('status', '!=', 'cancelled')
            ->with('club')
            ->limit(5)
            ->get();

        if ($todayParties->isEmpty()) return 0;

        $partyNames = $todayParties->take(3)->pluck('name')->join(', ');
        $verifiedCount = $todayParties->filter(fn (Party $party) => $party->is_verified_event)->count();
        $operatingCount = $todayParties->filter(fn (Party $party) => $party->is_operating_card)->count();
        $summaryParts = [];

        if ($verifiedCount > 0) {
            $summaryParts[] = "실이벤트 {$verifiedCount}개";
        }

        if ($operatingCount > 0) {
            $summaryParts[] = "운영형 카드 {$operatingCount}개";
        }

        $summaryText = empty($summaryParts) ? "오늘 {$todayParties->count()}개 파티" : implode(' · ', $summaryParts);

        $settings = NotificationSetting::where('enabled', true)
            ->where('tonight_recommendation', true)
            ->get();

        foreach ($settings as $setting) {
            // 오늘 이미 발송했는지 확인
            $exists = NiteNotification::where('session_id', $setting->session_id)
                ->where('type', NiteNotification::TYPE_TONIGHT_RECOMMENDATION)
                ->whereDate('created_at', $today)
                ->exists();

            if ($exists) continue;

            $this->create(
                sessionId: $setting->session_id,
                userId: $setting->user_id,
                type: NiteNotification::TYPE_TONIGHT_RECOMMENDATION,
                title: '오늘 밤, 어디로 갈까?',
                body: "{$summaryText}: {$partyNames}",
                link: '/parties?date=' . $today->toDateString(),
                data: [
                    'party_ids' => $todayParties->pluck('id')->toArray(),
                    'date'      => $today->toDateString(),
                    'verified_event_count' => $verifiedCount,
                    'operating_card_count' => $operatingCount,
                ],
            );
            $count++;
        }

        Log::info("TonightRecommendation: {$count}건 발송");
        return $count;
    }

    /**
     * 알림 생성 (in_app 채널)
     * 추후 웹 푸시 연결 시 이 메서드에 push 발송 로직 추가
     */
    private function create(
        string $sessionId,
        ?int $userId,
        string $type,
        string $title,
        string $body,
        ?string $link = null,
        ?array $data = null,
    ): NiteNotification {
        $notification = NiteNotification::create([
            'session_id' => $sessionId,
            'user_id'    => $userId,
            'type'       => $type,
            'title'      => $title,
            'body'       => $body,
            'link'       => $link,
            'data'       => $data,
            'channel'    => 'in_app',
            'sent_at'    => now(),
        ]);

        // ── 웹 푸시 연결 포인트 ──
        // $setting = NotificationSetting::where('session_id', $sessionId)->first();
        // if ($setting?->push_subscription) {
        //     WebPush::send($setting->push_subscription, [
        //         'title' => $title,
        //         'body'  => $body,
        //         'url'   => $link,
        //         'icon'  => '/icons/icon-192x192.png',
        //     ]);
        // }

        return $notification;
    }

    private function minutesToLabel(int $minutes): string
    {
        if ($minutes >= 1440) return (int)($minutes / 1440) . '일 후';
        if ($minutes >= 60) return (int)($minutes / 60) . '시간 후';
        return "{$minutes}분 후";
    }

    private function sendSavedFilterAlertsForParty(Party $party): int
    {
        if (!SavedFilter::available()) {
            return 0;
        }

        $count = 0;
        $savedFilters = SavedFilter::query()->ofType('party')->notifiable()->get();

        foreach ($savedFilters as $savedFilter) {
            if (!$this->notificationsEnabledForSession($savedFilter->session_id)) {
                continue;
            }

            if (!$this->partyMatchesSavedFilter($party, $savedFilter)) {
                continue;
            }

            $exists = NiteNotification::where('session_id', $savedFilter->session_id)
                ->where(function ($query) use ($savedFilter, $party) {
                    $query->where(function ($savedQuery) use ($party) {
                        $savedQuery->where('type', NiteNotification::TYPE_SAVED_FILTER_MATCH)
                            ->whereJsonContains('data->target_type', 'party')
                            ->whereJsonContains('data->target_id', $party->id);
                    })->orWhere(function ($matchQuery) use ($party) {
                        $matchQuery->where('type', NiteNotification::TYPE_NEW_PARTY_MATCH)
                            ->whereJsonContains('data->party_id', $party->id);
                    });
                })
                ->exists();

            if ($exists) {
                continue;
            }

            $this->create(
                sessionId: $savedFilter->session_id,
                userId: $savedFilter->user_id,
                type: NiteNotification::TYPE_SAVED_FILTER_MATCH,
                title: "저장한 필터와 맞는 새 파티 · {$party->event_card_label}",
                body: "{$savedFilter->name} 조건에 맞는 " . $this->partyLabelForMessage($party) . " {$party->name} 카드가 등록되었습니다.",
                link: "/parties/{$party->id}",
                data: [
                    'saved_filter_id' => $savedFilter->id,
                    'target_type' => 'party',
                    'target_id' => $party->id,
                    'event_card_type' => $party->event_card_type,
                    'event_card_label' => $party->event_card_label,
                    'event_card_notice' => $party->event_card_notice,
                ],
            );
            $count++;
        }

        return $count;
    }

    private function sendSavedFilterAlertsForClub(Club $club): int
    {
        if (!SavedFilter::available()) {
            return 0;
        }

        $count = 0;
        $savedFilters = SavedFilter::query()->ofType('club')->notifiable()->get();

        foreach ($savedFilters as $savedFilter) {
            if (!$this->notificationsEnabledForSession($savedFilter->session_id)) {
                continue;
            }

            if (!$this->clubMatchesSavedFilter($club, $savedFilter)) {
                continue;
            }

            $exists = NiteNotification::where('session_id', $savedFilter->session_id)
                ->where('type', NiteNotification::TYPE_SAVED_FILTER_MATCH)
                ->whereJsonContains('data->target_type', 'club')
                ->whereJsonContains('data->target_id', $club->id)
                ->exists();

            if ($exists) {
                continue;
            }

            $this->create(
                sessionId: $savedFilter->session_id,
                userId: $savedFilter->user_id,
                type: NiteNotification::TYPE_SAVED_FILTER_MATCH,
                title: '저장한 필터와 맞는 새 클럽',
                body: "{$savedFilter->name} 조건에 맞는 {$club->name} 클럽이 등록되었습니다.",
                link: "/clubs/{$club->id}",
                data: [
                    'saved_filter_id' => $savedFilter->id,
                    'target_type' => 'club',
                    'target_id' => $club->id,
                ],
            );
            $count++;
        }

        return $count;
    }

    private function notificationsEnabledForSession(string $sessionId): bool
    {
        $setting = NotificationSetting::where('session_id', $sessionId)->first();

        return $setting ? (bool) $setting->enabled : true;
    }

    private function partyLabelForMessage(Party $party): string
    {
        return match ($party->event_card_type) {
            'verified_event' => '실이벤트',
            'operating_card' => '운영형 대표 세션 카드',
            default => '일반 파티 카드',
        };
    }

    private function partyMatchesSavedFilter(Party $party, SavedFilter $savedFilter): bool
    {
        $filters = $savedFilter->filters ?? [];

        if (!empty($filters['date']) && $party->event_date?->toDateString() !== $filters['date']) {
            return false;
        }

        if (!empty($filters['area']) && ($party->club?->area !== $filters['area'])) {
            return false;
        }

        if (!empty($filters['genre']) && $party->genre !== $filters['genre']) {
            return false;
        }

        return true;
    }

    private function clubMatchesSavedFilter(Club $club, SavedFilter $savedFilter): bool
    {
        $filters = $savedFilter->filters ?? [];

        if (!empty($filters['area']) && $club->area !== $filters['area']) {
            return false;
        }

        if (!empty($filters['genre']) && $club->genre !== $filters['genre']) {
            return false;
        }

        if (!empty($filters['foreigner']) && !$club->foreigner_allowed) {
            return false;
        }

        return true;
    }
}
