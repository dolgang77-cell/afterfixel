@extends('layouts.app')
@section('title', $party->name)
@section('description', $party->name . ' · ' . ($party->club?->area ?? '') . ' ' . ($party->genre ?? '') . ' 파티. ' . ($party->event_date?->format('n/j') ?? '') . ' ' . ($party->start_time ?? ''))
@section('og_image', $party->thumbnail_url)
@section('page_type', 'party_detail')

@section('content')
@php
    $contactReady = (($inquiryConversion['assigned_md_count'] ?? 0) > 0) || (($inquiryConversion['avg_first_reply_minutes'] ?? null) !== null);
    $contactStatusLabel = $contactReady ? '문의 가능' : '문의 확인 필요';
    $contactStatusClass = $contactReady
        ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/20'
        : 'bg-amber-500/15 text-amber-300 border border-amber-500/20';
    $partyTimeDisplay = $party->time_range_text;
    $visitWindowLabel = $inquiryConversion['best_visit_window'] ?: ($party->event_date->format('n.j') . ' ' . $partyTimeDisplay);
    $budgetSummary = $inquiryConversion['budget_guide_min'] !== null && $inquiryConversion['budget_guide_max'] !== null
        ? number_format($inquiryConversion['budget_guide_min']) . '~' . number_format($inquiryConversion['budget_guide_max']) . '원'
        : '안내 확인';
@endphp
<div x-data="detailPageActions({
        shareTitle: @js($party->name),
        shareText: @js($party->name . ' · ' . ($party->club?->area ?? '') . ' ' . ($party->genre ?? '') . ' 파티'),
        shareUrl: @js(url()->current()),
        copiedText: @js('링크가 복사되었습니다.')
    })">
    <x-hero-image :image="$party->thumbnail_url" :alt="$party->name" :backRoute="route('parties.index')" height="h-56">
        @if($party->event_date->isToday())
            <div class="absolute top-4 right-4 z-10">
                <span class="text-[11px] font-bold gradient-accent text-white px-3 py-1.5 rounded-full shadow-glow animate-glow">TONIGHT</span>
            </div>
        @endif
        <div class="absolute bottom-4 left-4 z-10">
            <x-badge variant="purple" size="md" pill>{{ trans_auto($party->genre) }}</x-badge>
        </div>
    </x-hero-image>

    <div class="px-4 -mt-4 relative z-10 space-y-5 pb-36">
        {{-- Hero Summary --}}
        <div class="card overflow-hidden border border-white/[0.06] bg-[radial-gradient(circle_at_top_right,rgba(236,72,153,0.18),transparent_40%),linear-gradient(180deg,#171727_0%,#11111b_100%)] p-4"
             data-track-view-event="detail_summary_view"
             data-track-target-type="party"
             data-track-target-id="{{ $party->id }}"
             data-track-context="hero_summary">
            <div class="flex flex-wrap gap-2">
                <span class="rounded-full bg-white/8 px-3 py-1 text-[11px] font-semibold text-gray-200">{{ trans_auto($party->genre) }}</span>
                @if($party->club?->area)
                    <span class="rounded-full bg-white/8 px-3 py-1 text-[11px] font-semibold text-gray-200">{{ trans_auto($party->club->area) }}</span>
                @endif
                <span class="rounded-full px-3 py-1 text-[11px] font-semibold {{ $party->event_card_variant === 'green' ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/20' : ($party->event_card_variant === 'cyan' ? 'bg-cyan-500/15 text-cyan-300 border border-cyan-500/20' : 'bg-white/8 text-gray-200 border border-white/[0.08]') }}">{{ trans_auto($party->event_card_label) }}</span>
                <span class="rounded-full px-3 py-1 text-[11px] font-semibold {{ $contactStatusClass }}">{{ $contactStatusLabel }}</span>
                @if($party->event_date->isToday())
                    <span class="rounded-full bg-pink-500/15 px-3 py-1 text-[11px] font-semibold text-pink-300 border border-pink-500/20">오늘 진행</span>
                @endif
            </div>

            <div class="mt-4 flex items-start justify-between gap-3">
                <div class="flex-1 min-w-0">
                    <h1 class="text-[20px] font-extrabold tracking-tight text-white">{{ $party->name }}</h1>
                    @if($party->club)
                    <a href="{{ route('clubs.show', $party->club) }}" class="mt-1.5 inline-flex items-center gap-1 text-[13px] text-accent font-medium">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                        {{ $party->club->name }} · {{ trans_auto($party->club->area) }}
                    </a>
                    @endif
                    <p class="mt-2 text-[12px] text-gray-400">{{ $party->event_date->format('Y.n.j (D)') }} · {{ $partyTimeDisplay }}</p>
                </div>
                <div class="shrink-0 space-y-2">
                    <form action="{{ route('favorites.toggle', ['type' => 'party', 'id' => $party->id]) }}" method="POST" data-track-event="favorite_toggle" data-track-trigger="submit" data-track-target-type="party" data-track-target-id="{{ $party->id }}" data-track-context="hero_summary">
                        @csrf
                        <button type="submit" class="w-10 h-10 flex items-center justify-center rounded-full bg-dark-700/60 active:scale-90 transition-transform"
                                title="{{ $isFavorited ? __('common.fav_remove') : __('common.fav_add') }}">
                            @if($isFavorited)
                                <svg class="w-5 h-5 text-pink-500" fill="currentColor" viewBox="0 0 24 24"><path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.175 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/></svg>
                            @else
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                            @endif
                        </button>
                    </form>
                    <form action="{{ route('compare.toggle', ['type' => 'party', 'id' => $party->id]) }}" method="POST">
                        @csrf
                        <button type="submit" class="rounded-full border px-3 py-2 text-[10px] font-semibold {{ $isCompared ? 'border-violet-500/20 bg-violet-500/10 text-violet-200' : 'border-white/[0.08] bg-dark-700/60 text-gray-300' }}">
                            {{ $isCompared ? '비교중' : '비교' }}
                        </button>
                    </form>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-2.5">
                <div class="rounded-2xl border border-white/[0.06] bg-black/20 px-3 py-3">
                    <p class="text-[11px] text-gray-500">티켓 가격</p>
                    <p class="mt-1 text-[14px] font-bold text-white">{{ $party->ticket_price_text }}</p>
                    <p class="mt-1 text-[11px] text-gray-400">행사 기준 안내</p>
                </div>
                <div class="rounded-2xl border border-white/[0.06] bg-black/20 px-3 py-3">
                    <p class="text-[11px] text-gray-500">평균 응답</p>
                    <p class="mt-1 text-[14px] font-bold text-white">{{ $inquiryConversion['response_time_text'] }}</p>
                    <p class="mt-1 text-[11px] text-gray-400">{{ $inquiryConversion['response_hint'] }}</p>
                </div>
                <div class="rounded-2xl border border-white/[0.06] bg-black/20 px-3 py-3">
                    <p class="text-[11px] text-gray-500">진행 일정</p>
                    <p class="mt-1 text-[14px] font-bold text-white">{{ $party->event_date->format('n.j') }}</p>
                    <p class="mt-1 text-[11px] text-gray-400">{{ $partyTimeDisplay }}</p>
                </div>
                <div class="rounded-2xl border border-white/[0.06] bg-black/20 px-3 py-3">
                    <p class="text-[11px] text-gray-500">문의 흐름</p>
                    <p class="mt-1 text-[14px] font-bold text-white">{{ $inquiryConversion['availability_signal']['label'] }}</p>
                    <p class="mt-1 text-[11px] text-gray-400">{{ $inquiryConversion['availability_summary'] }}</p>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap gap-2">
                <span class="rounded-full bg-white/5 px-3 py-1 text-[11px] font-semibold text-emerald-300">{{ $inquiryConversion['availability_signal']['label'] }}</span>
                <span class="rounded-full bg-white/5 px-3 py-1 text-[11px] font-semibold text-violet-300">{{ $inquiryConversion['crowd_signal']['label'] }}</span>
                <span class="rounded-full bg-white/5 px-3 py-1 text-[11px] font-semibold text-sky-300">{{ $visitWindowLabel }}</span>
            </div>

            <div class="mt-4 rounded-2xl border border-white/[0.06] bg-black/20 px-3.5 py-3">
                <p class="text-[11px] font-semibold {{ $party->event_card_variant === 'green' ? 'text-emerald-300' : ($party->event_card_variant === 'cyan' ? 'text-cyan-300' : 'text-gray-300') }}">
                    {{ trans_auto($party->event_card_label) }}
                </p>
                <p class="mt-1 text-[12px] leading-6 text-gray-400">{{ trans_auto($party->event_card_notice) }}</p>
            </div>
        </div>

        {{-- Inquiry Snapshot --}}
        <div class="card p-4 space-y-3">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[14px] font-bold text-white">문의 전 빠른 확인</p>
                    <p class="mt-1 text-[12px] text-gray-400">오늘 입장 가능 여부, 예산, 답변 속도를 먼저 확인하고 바로 문의할 수 있게 정리했습니다.</p>
                </div>
                @if(($inquiryConversion['assigned_md_count'] ?? 0) > 0)
                    <span class="rounded-full bg-accent/15 px-3 py-1 text-[11px] font-semibold text-accent">{{ $inquiryConversion['assigned_md_count'] }}명 응대중</span>
                @endif
            </div>
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                <div class="rounded-2xl border border-white/[0.06] bg-dark-700/70 px-3 py-3">
                    <p class="text-[11px] text-gray-500">평균 응답시간</p>
                    <p class="mt-1 text-[14px] font-bold text-white">{{ $inquiryConversion['response_time_text'] }}</p>
                    <p class="mt-1 text-[11px] text-gray-400">{{ $inquiryConversion['response_hint'] }}</p>
                </div>
                <div class="rounded-2xl border border-white/[0.06] bg-dark-700/70 px-3 py-3">
                    <p class="text-[11px] text-gray-500">최근 확정률</p>
                    <p class="mt-1 text-[14px] font-bold text-white">{{ $inquiryConversion['confirmation_rate'] !== null ? $inquiryConversion['confirmation_rate'] . '%' : '준비중' }}</p>
                    <p class="mt-1 text-[11px] text-gray-400">{{ $inquiryConversion['confirmation_text'] }}</p>
                </div>
                <div class="rounded-2xl border border-white/[0.06] bg-dark-700/70 px-3 py-3">
                    <p class="text-[11px] text-gray-500">예상 가격대</p>
                    <p class="mt-1 text-[14px] font-bold text-white">{{ $budgetSummary }}</p>
                    <p class="mt-1 text-[11px] text-gray-400">{{ $inquiryConversion['budget_guide_text'] }}</p>
                </div>
            </div>
            @if($party->booking_link)
                <a href="{{ $party->booking_link }}" target="_blank" rel="noopener" class="btn-secondary flex items-center justify-center rounded-2xl px-4 py-3 text-[12px] font-semibold">
                    공식 예매 링크 열기
                </a>
            @endif
        </div>

        @include('partials.nearby-people-widget', [
            'venueType' => 'party',
            'venueId' => $party->id,
            'venueName' => $party->name,
            'contextLabel' => '같은 파티',
        ])

        <x-info-grid :items="[
            ['icon' => '📅', 'label' => __('party.date'), 'value' => $party->event_date->format('Y.n.j (D)')],
            ['icon' => '🕐', 'label' => __('party.time'), 'value' => $partyTimeDisplay],
            ['icon' => '🎫', 'label' => __('party.price'), 'value' => $party->ticket_price_text],
            ['icon' => '👔', 'label' => __('club.dress_code'), 'value' => $party->dress_code ?? __('club.free')],
        ]" />

        @include('partials.venue-faq-sheet', [
            'title' => '파티 운영 FAQ 빠르게 보기',
            'subtitle' => '드레스코드, 외국인 입장, 예매/테이블 문의, 입장 시간, 가격 안내를 한 번에 확인할 수 있습니다.',
            'items' => $faqItems,
            'targetType' => 'party',
            'targetId' => $party->id,
            'ctaHref' => auth()->check() ? '#detail-inquiry' : route('login'),
            'ctaLabel' => auth()->check() ? '문의로 이어가기' : '로그인 후 문의',
        ])

        @if($party->lineup)
        <div class="card p-4">
            <h3 class="text-[13px] font-bold text-gray-300 mb-2.5 flex items-center gap-1.5">
                <span class="w-5 h-5 rounded-lg gradient-purple flex items-center justify-center"><span class="text-[10px]">🎧</span></span>
                {{ __('party.lineup') }}
            </h3>
            <p class="text-[14px] text-gray-200 font-medium leading-relaxed">{{ $party->lineup }}</p>
        </div>
        @endif

        @if($party->entry_condition)
            <x-alert variant="warning">{{ __('party.entry_condition') }} {{ $party->entry_condition }}</x-alert>
        @endif

        @if($party->description)
        <div class="card p-4">
            <h3 class="text-[13px] font-bold text-gray-300 mb-2">{{ trans_auto($party->intro_title ?? '소개') }}</h3>
            @if($party->short_description)<p class="text-[14px] text-gray-300 font-semibold mb-2">{{ trans_auto($party->short_description) }}</p>@endif
            <p class="text-[13px] text-gray-400 leading-[1.7]">{{ trans_auto($party->description) }}</p>
            @if($party->full_description)
            <div x-data="{ open: false }" class="mt-3">
                <button x-on:click="open = !open" class="text-[12px] text-accent font-semibold" x-text="open ? '{{ __("common.close") }} ▲' : '{{ __("common.more") }} ▼'"></button>
                <div x-show="open" x-cloak class="rich-content mt-2 text-[13px] text-gray-400 leading-[1.7]">{!! \App\Services\RichContentService::render($party->full_description) !!}</div>
            </div>
            @endif
        </div>
        @endif

        @if($party->guide_text)
        <div class="card p-4">
            <h3 class="text-[13px] font-bold text-gray-300 mb-2">📋 {{ __('club.guide') }}</h3>
            <div class="rich-content text-[13px] text-gray-400 leading-[1.7]">{!! \App\Services\RichContentService::render($party->guide_text) !!}</div>
        </div>
        @endif

        @php
            $partyGallery = $party->approvedMedia()->get();
            $partyGalleryUrls = $partyGallery->pluck('file_url')->toArray();
            $partyGalleryThumbs = $partyGallery->map(fn($m) => $m->thumbnail_url ?? $m->file_url)->toArray();
            $partyGallerySrcsets = $partyGallery->pluck('file_srcset')->toArray();
            $partyGalleryThumbSrcsets = $partyGallery->map(fn($m) => $m->thumbnail_srcset ?? $m->file_srcset)->toArray();
        @endphp
        @if($partyGallery->isNotEmpty())
        <div class="card p-4">
            <h3 class="text-[13px] font-bold text-gray-300 mb-3">📸 {{ __('club.gallery') }}</h3>
            <x-image-gallery :images="$partyGalleryUrls" :thumbnails="$partyGalleryThumbs" :srcsets="$partyGallerySrcsets" :thumbnailSrcsets="$partyGalleryThumbSrcsets" />
        </div>
        @endif

        <div>
            @include('partials.review-section', [
                'reviewKey' => 'party-' . $party->id,
                'reviewAction' => route('reviews.store', ['type' => 'party', 'id' => $party->id]),
                'reviews' => $reviews,
                'reviewSummary' => $reviewSummary,
            ])
        </div>

        <x-tag-list :tags="$party->tags ?? []" />

        {{-- 연결된 MD --}}
        @if($party->activeMds->isNotEmpty())
        <div>
            <h2 class="text-[15px] font-bold flex items-center gap-2 mb-3 tracking-tight">
                <span class="text-accent">🎤</span> {{ __('md.connected') }}
            </h2>
            <div class="space-y-2">
                @foreach($party->activeMds as $md)
                    <x-md-card :md="$md" />
                @endforeach
            </div>
        </div>
        @endif

        {{-- 예약 문의 --}}
        <div id="detail-inquiry">
            <h2 class="text-[15px] font-bold flex items-center gap-2 mb-3 tracking-tight"><span>💬</span> {{ __('inquiry.title') }}</h2>
            @include('partials.inquiry-form', [
                'action' => route('inquiries.store', ['type' => 'party', 'id' => $party->id]),
                'formId' => 'party-inquiry',
                'helperText' => '입장 가능 여부나 예산, 방문 시간만 남겨도 충분합니다. 나머지는 상담에서 맞춰집니다.',
                'messagePlaceholder' => '예: 오늘 2명 입장 가능할까요? 대기 여부도 같이 알고 싶어요.',
                'budgetGuideText' => $inquiryConversion['budget_guide_text'],
                'trackTargetType' => 'party',
                'trackTargetId' => $party->id,
            ])
        </div>

        {{-- CTAs --}}
        <div class="space-y-2.5 pt-1">
            <a href="{{ route('tour.index', ['area' => $party->club?->area]) }}"
               class="btn-secondary block w-full text-center py-3.5 rounded-2xl font-bold text-[13px]">
                {{ __('party.add_tour') }}
            </a>
        </div>
    </div>

    <div class="fixed inset-x-0 bottom-[72px] z-40 px-4 md:hidden">
        <div class="mx-auto max-w-lg">
            <div class="relative">
                <div x-show="notice" x-transition.opacity.duration.200ms class="pointer-events-none absolute -top-11 left-1/2 -translate-x-1/2 rounded-full bg-dark-900/95 px-3 py-1.5 text-[11px] font-semibold text-white border border-white/[0.08]">
                    <span x-text="notice"></span>
                </div>
                <div class="glass rounded-[1.4rem] border border-white/[0.08] p-2.5 shadow-card">
                    <div class="grid grid-cols-[48px_48px_minmax(0,1fr)] gap-2">
                        <form action="{{ route('favorites.toggle', ['type' => 'party', 'id' => $party->id]) }}" method="POST" data-track-event="favorite_toggle" data-track-trigger="submit" data-track-target-type="party" data-track-target-id="{{ $party->id }}" data-track-context="sticky_footer">
                            @csrf
                            <button type="submit" class="flex h-12 w-12 items-center justify-center rounded-2xl bg-dark-700/80 text-gray-300 border border-white/[0.06]">
                                @if($isFavorited)
                                    <svg class="w-5 h-5 text-pink-500" fill="currentColor" viewBox="0 0 24 24"><path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.175 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/></svg>
                                @else
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                                @endif
                            </button>
                        </form>
                        <button type="button" @click="share()" class="flex h-12 w-12 items-center justify-center rounded-2xl bg-dark-700/80 text-gray-300 border border-white/[0.06]" data-track-event="share_click" data-track-target-type="party" data-track-target-id="{{ $party->id }}" data-track-context="sticky_footer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 11-1.434-4.266 2.25 2.25 0 011.434 4.266zM18.75 8.25a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zM18.75 18a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zM8.89 9.932l5.57-2.828M8.89 14.068l5.57 2.828"/></svg>
                        </button>
                        @auth
                            <a href="#detail-inquiry" class="btn-primary flex h-12 items-center justify-center rounded-2xl px-4 text-[13px] font-bold text-white" data-track-event="detail_sticky_cta_click" data-track-target-type="party" data-track-target-id="{{ $party->id }}" data-track-context="inquiry">
                                문의하기
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn-primary flex h-12 items-center justify-center rounded-2xl px-4 text-[13px] font-bold text-white" data-track-event="detail_sticky_cta_click" data-track-target-type="party" data-track-target-id="{{ $party->id }}" data-track-context="login_required">
                                로그인 후 문의
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
window.detailPageActions = window.detailPageActions || function (config) {
    return {
        notice: '',
        noticeTimer: null,
        showNotice(message) {
            this.notice = message;
            clearTimeout(this.noticeTimer);
            this.noticeTimer = setTimeout(() => {
                this.notice = '';
            }, 1800);
        },
        async share() {
            try {
                if (navigator.share) {
                    await navigator.share({
                        title: config.shareTitle,
                        text: config.shareText,
                        url: config.shareUrl,
                    });
                    return;
                }

                if (navigator.clipboard?.writeText) {
                    await navigator.clipboard.writeText(config.shareUrl);
                    this.showNotice(config.copiedText || '링크가 복사되었습니다.');
                    return;
                }
            } catch (error) {
                if (error?.name === 'AbortError') {
                    return;
                }
            }

            window.prompt('링크를 복사하세요', config.shareUrl);
        },
    };
};
</script>
@endpush
