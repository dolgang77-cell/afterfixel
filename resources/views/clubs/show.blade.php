@extends('layouts.app')
@section('title', $club->name)
@section('description', $club->name . ' · ' . $club->area . ' ' . $club->genre . ' 클럽. ' . ($club->description ? mb_substr($club->description, 0, 80) : '오늘 밤 갈 곳을 고르는 가장 빠른 가이드'))
@section('og_image', $club->thumbnail_url)
@section('page_type', 'club_detail')

@section('content')
@php
    $contactReady = (($inquiryConversion['assigned_md_count'] ?? 0) > 0) || (($inquiryConversion['avg_first_reply_minutes'] ?? null) !== null);
    $contactStatusLabel = $contactReady ? '문의 가능' : '문의 확인 필요';
    $contactStatusClass = $contactReady
        ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/20'
        : 'bg-amber-500/15 text-amber-300 border border-amber-500/20';
    $clubHoursDisplay = $club->operating_hours_text;
    $visitWindowLabel = $inquiryConversion['best_visit_window'] ?: $clubHoursDisplay;
    $budgetSummary = $inquiryConversion['budget_guide_min'] !== null && $inquiryConversion['budget_guide_max'] !== null
        ? number_format($inquiryConversion['budget_guide_min']) . '~' . number_format($inquiryConversion['budget_guide_max']) . '원'
        : '안내 확인';
@endphp
<div x-data="detailPageActions({
        shareTitle: @js($club->name),
        shareText: @js($club->name . ' · ' . $club->area . ' ' . $club->genre . ' 클럽'),
        shareUrl: @js(url()->current()),
        copiedText: @js('링크가 복사되었습니다.')
    })">
    <x-hero-image :image="$club->thumbnail_url" :alt="$club->name" :backRoute="route('clubs.index')" height="h-60">
        {{-- Overlay badges --}}
        <div class="absolute bottom-4 left-4 z-10 flex items-center gap-2">
            @if($club->foreigner_allowed)
                <span class="text-[10px] font-semibold bg-blue-500/20 text-blue-300 px-2.5 py-1 rounded-full backdrop-blur-md border border-blue-500/15">🌍 {{ __('club.foreigner_ok') }}</span>
            @endif
            @if($club->vibe)
                <span class="text-[10px] font-semibold bg-black/30 text-white/80 px-2.5 py-1 rounded-full backdrop-blur-md border border-white/10">{{ trans_auto($club->vibe) }}</span>
            @endif
        </div>
    </x-hero-image>

    <div class="px-4 -mt-4 relative z-10 space-y-5 pb-36">
        {{-- Hero Summary --}}
        <div class="card overflow-hidden border border-white/[0.06] bg-[radial-gradient(circle_at_top_right,rgba(168,85,247,0.24),transparent_42%),linear-gradient(180deg,#171727_0%,#11111b_100%)] p-4"
             data-track-view-event="detail_summary_view"
             data-track-target-type="club"
             data-track-target-id="{{ $club->id }}"
             data-track-context="hero_summary">
            <div class="flex flex-wrap gap-2">
                <span class="rounded-full bg-white/8 px-3 py-1 text-[11px] font-semibold text-gray-200">{{ trans_auto($club->area) }}</span>
                <span class="rounded-full bg-white/8 px-3 py-1 text-[11px] font-semibold text-gray-200">{{ trans_auto($club->genre) }}{{ $club->subgenre ? ' / ' . trans_auto($club->subgenre) : '' }}</span>
                <span class="rounded-full px-3 py-1 text-[11px] font-semibold {{ $contactStatusClass }}">{{ $contactStatusLabel }}</span>
                @if($club->foreigner_allowed)
                    <span class="rounded-full bg-sky-500/15 px-3 py-1 text-[11px] font-semibold text-sky-300 border border-sky-500/20">{{ __('club.foreigner_ok') }}</span>
                @endif
            </div>

            <div class="mt-4 flex items-start justify-between gap-3">
                <div class="flex-1 min-w-0">
                    <h1 class="text-[22px] font-extrabold tracking-tight text-white">{{ $club->name }}</h1>
                    <p class="mt-1 text-[13px] text-gray-400">{{ $club->address ?: trans_auto($club->area) . ' 입장 안내 제공' }}</p>
                    <div class="mt-3 flex flex-wrap items-center gap-3">
                        <x-rating-stars :rating="$club->rating_avg" :count="$club->rating_count" size="md" />
                        @if($club->vibe)
                            <span class="text-[11px] font-medium text-gray-300">{{ trans_auto($club->vibe) }}</span>
                        @endif
                    </div>
                </div>
                <div class="shrink-0 space-y-2">
                    <form action="{{ route('favorites.toggle', ['type' => 'club', 'id' => $club->id]) }}" method="POST" data-track-event="favorite_toggle" data-track-trigger="submit" data-track-target-type="club" data-track-target-id="{{ $club->id }}" data-track-context="hero_summary">
                        @csrf
                        <button type="submit" class="w-10 h-10 flex items-center justify-center rounded-full bg-dark-700/60 active:scale-90 transition-transform"
                                title="{{ $isFavorited ? __('common.fav_remove') : __('common.fav_add') }}">
                            @if($isFavorited)
                                <svg class="w-5 h-5 text-pink-500" fill="currentColor" viewBox="0 0 24 24"><path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/></svg>
                            @else
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                            @endif
                        </button>
                    </form>
                    <form action="{{ route('compare.toggle', ['type' => 'club', 'id' => $club->id]) }}" method="POST">
                        @csrf
                        <button type="submit" class="rounded-full border px-3 py-2 text-[10px] font-semibold {{ $isCompared ? 'border-violet-500/20 bg-violet-500/10 text-violet-200' : 'border-white/[0.08] bg-dark-700/60 text-gray-300' }}">
                            {{ $isCompared ? '비교중' : '비교' }}
                        </button>
                    </form>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-2.5">
                <div class="rounded-2xl border border-white/[0.06] bg-black/20 px-3 py-3">
                    <p class="text-[11px] text-gray-500">입장 가격</p>
                    <p class="mt-1 text-[14px] font-bold text-white">{{ $club->entry_fee_text }}</p>
                    <p class="mt-1 text-[11px] text-gray-400">현장 기준 안내</p>
                </div>
                <div class="rounded-2xl border border-white/[0.06] bg-black/20 px-3 py-3">
                    <p class="text-[11px] text-gray-500">평균 응답</p>
                    <p class="mt-1 text-[14px] font-bold text-white">{{ $inquiryConversion['response_time_text'] }}</p>
                    <p class="mt-1 text-[11px] text-gray-400">{{ $inquiryConversion['response_hint'] }}</p>
                </div>
                <div class="rounded-2xl border border-white/[0.06] bg-black/20 px-3 py-3">
                    <p class="text-[11px] text-gray-500">운영 시간</p>
                    <p class="mt-1 text-[14px] font-bold text-white">{{ $clubHoursDisplay }}</p>
                    <p class="mt-1 text-[11px] text-gray-400">오늘 방문 기준</p>
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
        </div>

        {{-- Inquiry Snapshot --}}
        <div class="card p-4 space-y-3">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[14px] font-bold text-white">문의 전 빠른 확인</p>
                    <p class="mt-1 text-[12px] text-gray-400">가격, 응답 속도, 최근 확정 흐름을 먼저 보고 바로 문의할 수 있게 정리했습니다.</p>
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
        </div>

        @include('partials.nearby-people-widget', [
            'venueType' => 'club',
            'venueId' => $club->id,
            'venueName' => $club->name,
            'contextLabel' => '같은 클럽',
        ])

        {{-- Info Grid --}}
        <x-info-grid :items="[
            ['icon' => '🕐', 'label' => __('club.hours'), 'value' => $clubHoursDisplay],
            ['icon' => '💰', 'label' => __('club.entry_fee'), 'value' => $club->entry_fee_text],
            ['icon' => '👔', 'label' => __('club.dress_code'), 'value' => $club->dress_code ?? __('club.free')],
            ['icon' => '📍', 'label' => __('club.location'), 'value' => $club->area],
        ]" />

        @include('partials.venue-faq-sheet', [
            'title' => '운영 FAQ 빠르게 보기',
            'subtitle' => '드레스코드, 외국인 입장, 테이블 문의, 방문 시간, 비용 안내를 먼저 확인할 수 있습니다.',
            'items' => $faqItems,
            'targetType' => 'club',
            'targetId' => $club->id,
            'ctaHref' => auth()->check() ? '#detail-inquiry' : route('login'),
            'ctaLabel' => auth()->check() ? '문의로 이어가기' : '로그인 후 문의',
        ])

        @if($club->dress_code_detail)
            <x-alert variant="warning">{{ __('club.dress_notice') }} {{ $club->dress_code_detail }}</x-alert>
        @endif

        {{-- Description --}}
        @if($club->description)
        <div class="card p-4">
            <h3 class="text-[13px] font-bold text-gray-300 mb-2">{{ trans_auto($club->intro_title ?? '소개') }}</h3>
            @if($club->short_description)
                <p class="text-[14px] text-gray-300 font-semibold mb-2">{{ trans_auto($club->short_description) }}</p>
            @endif
            <p class="text-[13px] text-gray-400 leading-[1.7]">{{ trans_auto($club->description) }}</p>
            @if($club->full_description)
            <div x-data="{ open: false }" class="mt-3">
                <button x-on:click="open = !open" class="text-[12px] text-accent font-semibold" x-text="open ? '{{ __("common.close") }} ▲' : '{{ __("common.more") }} ▼'"></button>
                <div x-show="open" x-cloak class="rich-content mt-2 text-[13px] text-gray-400 leading-[1.7]">{!! \App\Services\RichContentService::render($club->full_description) !!}</div>
            </div>
            @endif
        </div>
        @endif

        {{-- 이용 가이드 --}}
        @if($club->guide_text)
        <div class="card p-4">
            <h3 class="text-[13px] font-bold text-gray-300 mb-2 flex items-center gap-1.5">📋 {{ __('club.guide') }}</h3>
            <div class="rich-content text-[13px] text-gray-400 leading-[1.7]">{!! \App\Services\RichContentService::render($club->guide_text) !!}</div>
        </div>
        @endif

        {{-- 승인된 이미지 갤러리 --}}
        @php
            $galleryImages = $club->approvedMedia()->get();
            $galleryUrls = $galleryImages->pluck('file_url')->toArray();
            $galleryThumbs = $galleryImages->map(fn($m) => $m->thumbnail_url ?? $m->file_url)->toArray();
            $gallerySrcsets = $galleryImages->pluck('file_srcset')->toArray();
            $galleryThumbSrcsets = $galleryImages->map(fn($m) => $m->thumbnail_srcset ?? $m->file_srcset)->toArray();
        @endphp
        @if($galleryImages->isNotEmpty())
        <div class="card p-4">
            <h3 class="text-[13px] font-bold text-gray-300 mb-3">📸 {{ __('club.gallery') }}</h3>
            <x-image-gallery :images="$galleryUrls" :thumbnails="$galleryThumbs" :srcsets="$gallerySrcsets" :thumbnailSrcsets="$galleryThumbSrcsets" />
        </div>
        @endif

        <div>
            @include('partials.review-section', [
                'reviewKey' => 'club-' . $club->id,
                'reviewAction' => route('reviews.store', ['type' => 'club', 'id' => $club->id]),
                'reviews' => $reviews,
                'reviewSummary' => $reviewSummary,
            ])
        </div>

        {{-- Address --}}
        @if($club->address)
        <div class="card p-4">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 shrink-0 rounded-xl bg-dark-600/50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                </div>
                <div>
                    <p class="text-[13px] text-gray-300">{{ $club->address }}</p>
                    @if($club->instagram)
                    <p class="text-[12px] text-accent mt-1 font-medium">{{ $club->instagram }}</p>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <x-tag-list :tags="$club->tags ?? []" />

        {{-- Upcoming Parties --}}
        @if($club->upcomingParties->isNotEmpty())
        <div>
            <x-section-header :title="__('club.upcoming_party')" badge="🎉" />
            <div class="space-y-2">
                @foreach($club->upcomingParties as $party)
                <a href="{{ route('parties.show', $party) }}" class="card flex items-center gap-3.5 p-3.5 group">
                    <div class="w-11 h-11 shrink-0 rounded-2xl gradient-pink flex flex-col items-center justify-center shadow-glow-pink">
                        <span class="text-[14px] font-extrabold text-white leading-none">{{ $party->event_date->format('d') }}</span>
                        <span class="text-[8px] text-white/70 font-semibold uppercase">{{ $party->event_date->format('M') }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-semibold text-gray-200 truncate">{{ $party->name }}</p>
                        <p class="text-[11px] text-gray-500">{{ $party->time_range_text }} · {{ $party->ticket_price_text }}</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-700 shrink-0 group-hover:text-accent transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- 예약 문의 --}}
        <div id="detail-inquiry">
            <h2 class="text-[15px] font-bold flex items-center gap-2 mb-3 tracking-tight">
                <span>💬</span> {{ __('inquiry.title') }}
            </h2>
            @include('partials.inquiry-form', [
                'action' => route('inquiries.store', ['type' => 'club', 'id' => $club->id]),
                'formId' => 'club-inquiry',
                'helperText' => '방문 일정이 확정되지 않아도 됩니다. 궁금한 점만 남기면 바로 접수됩니다.',
                'messagePlaceholder' => '예: 오늘 4명 입장 가능할까요? 테이블 안내도 함께 받고 싶어요.',
                'budgetGuideText' => $inquiryConversion['budget_guide_text'],
                'trackTargetType' => 'club',
                'trackTargetId' => $club->id,
            ])
        </div>

        {{-- 담당 MD --}}
        @if($club->activeMds->isNotEmpty())
        <div>
            <h2 class="text-[15px] font-bold flex items-center gap-2 mb-3 tracking-tight">
                <span class="text-accent">🎤</span> {{ __('md.assigned') }}
            </h2>
            <div class="space-y-2">
                @foreach($club->activeMds as $md)
                    <x-md-card :md="$md" />
                @endforeach
            </div>
        </div>
        @endif

        {{-- CTA --}}
        <a href="{{ route('tour.index', ['area' => $club->area]) }}"
           class="btn-primary block w-full text-center py-3.5 rounded-2xl font-bold text-[14px] text-white">
            {{ __('club.start_tour') }}
        </a>
    </div>

    <div class="fixed inset-x-0 bottom-[72px] z-40 px-4 md:hidden">
        <div class="mx-auto max-w-lg">
            <div class="relative">
                <div x-show="notice" x-transition.opacity.duration.200ms class="pointer-events-none absolute -top-11 left-1/2 -translate-x-1/2 rounded-full bg-dark-900/95 px-3 py-1.5 text-[11px] font-semibold text-white border border-white/[0.08]">
                    <span x-text="notice"></span>
                </div>
                <div class="glass rounded-[1.4rem] border border-white/[0.08] p-2.5 shadow-card">
                    <div class="grid grid-cols-[48px_48px_minmax(0,1fr)] gap-2">
                        <form action="{{ route('favorites.toggle', ['type' => 'club', 'id' => $club->id]) }}" method="POST" data-track-event="favorite_toggle" data-track-trigger="submit" data-track-target-type="club" data-track-target-id="{{ $club->id }}" data-track-context="sticky_footer">
                            @csrf
                            <button type="submit" class="flex h-12 w-12 items-center justify-center rounded-2xl bg-dark-700/80 text-gray-300 border border-white/[0.06]">
                                @if($isFavorited)
                                    <svg class="w-5 h-5 text-pink-500" fill="currentColor" viewBox="0 0 24 24"><path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/></svg>
                                @else
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                                @endif
                            </button>
                        </form>
                        <button type="button" @click="share()" class="flex h-12 w-12 items-center justify-center rounded-2xl bg-dark-700/80 text-gray-300 border border-white/[0.06]" data-track-event="share_click" data-track-target-type="club" data-track-target-id="{{ $club->id }}" data-track-context="sticky_footer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 11-1.434-4.266 2.25 2.25 0 011.434 4.266zM18.75 8.25a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zM18.75 18a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zM8.89 9.932l5.57-2.828M8.89 14.068l5.57 2.828"/></svg>
                        </button>
                        @auth
                            <a href="#detail-inquiry" class="btn-primary flex h-12 items-center justify-center rounded-2xl px-4 text-[13px] font-bold text-white" data-track-event="detail_sticky_cta_click" data-track-target-type="club" data-track-target-id="{{ $club->id }}" data-track-context="inquiry">
                                문의하기
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn-primary flex h-12 items-center justify-center rounded-2xl px-4 text-[13px] font-bold text-white" data-track-event="detail_sticky_cta_click" data-track-target-type="club" data-track-target-id="{{ $club->id }}" data-track-context="login_required">
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
