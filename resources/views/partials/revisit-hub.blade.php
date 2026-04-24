@props([
    'hub',
    'surface' => 'home',
    'title' => '다시 이어가기',
    'subtitle' => '최근 본, 찜, 알림, 문의를 다시 묶어 오늘 밤 흐름으로 이어줍니다.',
    'showAlways' => false,
])

@php
    $surfacePrefix = $surface === 'home' ? 'home' : 'my';
    $defaultCardEvent = $surface === 'home' ? 'home_revisit_click' : 'my_action_card_click';
    $shouldShow = $showAlways || ($hub['hasSignals'] ?? false);

    $continuePrimary = $hub['continuePrimary'] ?? null;
    $favoritePrimary = $hub['favoritePrimary'] ?? null;
    $topInquiry = $hub['topInquiry'] ?? null;
    $unreadCount = $hub['unreadCount'] ?? 0;
    $openInquiryCount = $hub['openInquiryCount'] ?? 0;
    $pendingReplyCount = $hub['pendingReplyCount'] ?? 0;
    $showInquiryCard = auth()->check() || $openInquiryCount > 0;
    $showNotificationCard = auth()->check() || $unreadCount > 0;
@endphp

@if($shouldShow)
<section class="px-4 pt-1">
    <div class="flex items-start justify-between gap-3 mb-3">
        <div>
            <h2 class="text-[17px] font-bold tracking-tight text-white">{{ $title }}</h2>
            <p class="mt-1 text-[11px] text-gray-500">{{ $subtitle }}</p>
        </div>
        @if($openInquiryCount > 0)
            <span class="rounded-full bg-accent/15 px-3 py-1 text-[11px] font-semibold text-accent">진행중 {{ $openInquiryCount }}건</span>
        @endif
    </div>

    @if($showInquiryCard || $showNotificationCard)
        <div class="grid grid-cols-2 gap-2.5">
            @if($showInquiryCard)
                <a href="{{ auth()->check() ? route('my.inquiries') : route('login') }}"
                   class="card p-4 border border-cyan-500/15 bg-[radial-gradient(circle_at_top_right,rgba(34,211,238,0.16),transparent_40%),#161622]"
                   data-track-event="{{ $defaultCardEvent }}"
                   data-track-context="active_inquiries"
                   data-track-label="진행중 문의">
                    <div class="w-9 h-9 rounded-2xl bg-cyan-500/10 flex items-center justify-center text-[16px]">💬</div>
                    <p class="mt-3 text-[13px] font-semibold text-white">진행중 문의</p>
                    <p class="mt-1 text-[22px] font-black text-cyan-300">{{ $openInquiryCount }}</p>
                    <p class="mt-1 text-[11px] text-gray-500">
                        @if($topInquiry)
                            답변 대기 {{ $pendingReplyCount }}건 · {{ $topInquiry->subject }}
                        @elseif(auth()->check())
                            진행중인 문의가 없으면 새 상담 흐름이 여기에 표시됩니다.
                        @else
                            로그인 후 상담 내역과 답변을 바로 이어볼 수 있습니다.
                        @endif
                    </p>
                </a>
            @endif

            @if($showNotificationCard)
                <a href="{{ auth()->check() ? route('notifications.index') : route('login') }}"
                   class="card p-4"
                   data-track-event="{{ $defaultCardEvent }}"
                   data-track-context="notifications"
                   data-track-label="읽지 않은 알림">
                    <div class="w-9 h-9 rounded-2xl bg-blue-500/10 flex items-center justify-center text-[16px]">🔔</div>
                    <p class="mt-3 text-[13px] font-semibold text-white">읽지 않은 알림</p>
                    <p class="mt-1 text-[22px] font-black text-blue-300">{{ $unreadCount }}</p>
                    <p class="mt-1 text-[11px] text-gray-500">
                        @if(auth()->check())
                            {{ $unreadCount > 0 ? '확인 필요한 알림이 있습니다.' : '새 알림이 없습니다.' }}
                        @else
                            로그인 후 도착한 알림과 응답을 한 번에 확인할 수 있습니다.
                        @endif
                    </p>
                </a>
            @endif
        </div>
    @endif

    <div class="mt-2.5 grid grid-cols-1 gap-2.5">
        @if($continuePrimary)
            @php
                $continueTarget = $continuePrimary->target;
                $continueUrl = $continuePrimary->type === 'club'
                    ? route('clubs.show', $continueTarget)
                    : ($continuePrimary->type === 'party' ? route('parties.show', $continueTarget) : route('tour.index'));
                $continueMeta = match ($continuePrimary->type) {
                    'club' => trans_auto($continueTarget->area) . ' · ' . trans_auto($continueTarget->genre),
                    'party' => ($continueTarget->club?->name ? $continueTarget->club->name . ' · ' : '') . (($continueTarget->event_date?->format('n/j')) ?? trans_auto($continueTarget->genre)),
                    default => trans_auto('최근 추천 경로'),
                };
            @endphp
            <a href="{{ $continueUrl }}"
               class="card p-4 border border-violet-500/15 bg-[radial-gradient(circle_at_top_right,rgba(139,92,246,0.16),transparent_40%),#161622]"
               data-track-event="{{ $surfacePrefix }}_recent_continue_click"
               data-track-context="primary"
               data-track-target-type="{{ $continuePrimary->type }}"
               data-track-target-id="{{ $continueTarget->id }}"
               data-track-label="{{ $continueTarget->name ?? 'tour' }}">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-2xl bg-violet-500/10 flex items-center justify-center text-[17px]">🕐</div>
                        <div class="min-w-0">
                            <p class="text-[13px] font-semibold text-white">최근 본 이어보기</p>
                            <p class="mt-1 text-[11px] text-gray-500 truncate">{{ $continueTarget->name ?? '추천 경로' }}</p>
                            <p class="mt-1 text-[11px] text-gray-500 truncate">{{ $continueMeta }}</p>
                        </div>
                    </div>
                    <span class="text-[11px] font-semibold text-violet-300">열기</span>
                </div>
            </a>
        @endif

        @if($favoritePrimary)
            @php
                $favoriteTarget = $favoritePrimary->target;
                $favoriteUrl = $favoritePrimary->type === 'club'
                    ? route('clubs.show', $favoriteTarget)
                    : ($favoritePrimary->type === 'party' ? route('parties.show', $favoriteTarget) : route('favorites.index'));
                $favoriteMeta = match ($favoritePrimary->type) {
                    'club' => trans_auto($favoriteTarget->area) . ' · ' . trans_auto($favoriteTarget->genre),
                    'party' => ($favoriteTarget->club?->name ? $favoriteTarget->club->name . ' · ' : '') . (($favoriteTarget->event_date?->format('n/j')) ?? trans_auto($favoriteTarget->genre)),
                    default => trans_auto('찜한 후보'),
                };
            @endphp
            <a href="{{ $favoriteUrl }}"
               class="card p-4 border border-pink-500/15 bg-[radial-gradient(circle_at_top_right,rgba(244,114,182,0.16),transparent_40%),#161622]"
               data-track-event="{{ $defaultCardEvent }}"
               data-track-context="favorites"
               data-track-target-type="{{ $favoritePrimary->type }}"
               data-track-target-id="{{ $favoriteTarget->id }}"
               data-track-label="{{ $favoriteTarget->name ?? 'favorite' }}">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-2xl bg-pink-500/10 flex items-center justify-center text-[17px]">❤️</div>
                        <div class="min-w-0">
                            <p class="text-[13px] font-semibold text-white">최근 찜한 후보</p>
                            <p class="mt-1 text-[11px] text-gray-500 truncate">{{ $favoriteTarget->name ?? '찜한 항목' }}</p>
                            <p class="mt-1 text-[11px] text-gray-500 truncate">{{ $favoriteMeta }}</p>
                        </div>
                    </div>
                    <span class="text-[11px] font-semibold text-pink-300">열기</span>
                </div>
            </a>
        @endif
    </div>
</section>
@endif
