@extends('layouts.app')
@section('title', $q ? "{$q} " . __('nav.search') : __('nav.search'))

@section('content')
@php
    $recommendedKeywords = ['홍대', '부산 서면', '부산 광안리', '영종도', '대구 동성로', __('search.today_party')];
@endphp
<div class="px-4 py-5 space-y-5">

    {{-- 검색바 --}}
    <form action="{{ route('search') }}" method="GET">
        <div class="relative">
            <svg class="w-5 h-5 text-gray-500 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input type="text" name="q" value="{{ $q }}" autofocus
                   class="w-full pl-11 pr-4 py-3.5 rounded-2xl bg-dark-700 border border-white/[0.06] text-[14px] text-white placeholder-gray-600 focus:border-accent focus:outline-none"
                   placeholder="{{ __('search.placeholder') }}">
        </div>
    </form>

    @if(!$q)
        {{-- 인기 검색어 --}}
        @if($popular->isNotEmpty())
        <div>
            <h2 class="text-[14px] font-bold text-gray-300 mb-3">{{ __('search.popular') }}</h2>
            <div class="flex flex-wrap gap-2">
                @foreach($popular as $kw)
                <a href="{{ route('search', ['q' => $kw]) }}" class="px-3.5 py-2 rounded-full bg-dark-700 border border-white/[0.06] text-[12px] text-gray-400 font-medium active:scale-95 transition-transform">{{ $kw }}</a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- 추천 키워드 --}}
        <div>
            <h2 class="text-[14px] font-bold text-gray-300 mb-3">{{ __('search.recommended') }}</h2>
            <div class="flex flex-wrap gap-2">
                @foreach($recommendedKeywords as $kw)
                <a href="{{ route('search', ['q' => $kw]) }}" class="px-3.5 py-2 rounded-full bg-accent/10 border border-accent/20 text-[12px] text-accent font-semibold active:scale-95 transition-transform">{{ $kw }}</a>
                @endforeach
            </div>
        </div>
    @else
        @php $total = $clubs->count() + $parties->count() + $mds->count(); @endphp

        @if($total === 0)
        <div class="card p-8 text-center">
            <p class="text-[14px] text-gray-400">{{ __('search.no_results') }}</p>
            <p class="text-[12px] text-gray-600 mt-1">{{ __('search.no_results_desc') }}</p>
        </div>
        @endif

        {{-- 클럽 결과 --}}
        @if($clubs->isNotEmpty())
        <div>
            <h2 class="text-[15px] font-bold text-white mb-3">{{ __('nav.club') }} <span class="text-[12px] text-gray-500 font-normal">{{ $clubs->count() }}{{ __('search.results_count') }}</span></h2>
            <div class="space-y-2">
                @foreach($clubs as $club)
                <a href="{{ route('clubs.show', $club) }}" class="card flex items-center gap-3.5 p-3.5">
                    <div class="w-12 h-12 rounded-xl overflow-hidden shrink-0 bg-dark-700">
                        @php $thumbnailSrcset = $club->thumbnail_srcset; @endphp
                        <img src="{{ $club->thumbnail_url }}" class="w-full h-full object-cover" loading="lazy" decoding="async"
                             @if($thumbnailSrcset) srcset="{{ $thumbnailSrcset }}" sizes="48px" @endif>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-semibold text-gray-200 truncate">{{ $club->name }}</p>
                        <p class="text-[11px] text-gray-500">{{ trans_auto($club->area) }} · {{ trans_auto($club->genre) }}</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- 파티 결과 --}}
        @if($parties->isNotEmpty())
        <div>
            <div class="mb-3 space-y-2">
                <h2 class="text-[15px] font-bold text-white">{{ __('nav.party') }} <span class="text-[12px] text-gray-500 font-normal">{{ $parties->count() }}{{ __('search.results_count') }}</span></h2>
                <div class="flex flex-wrap items-center gap-2 text-[11px] text-gray-400">
                    <x-badge variant="green" size="xs" pill>{{ trans_auto('실이벤트') }}</x-badge>
                    <span>{{ trans_auto('외부 공지·공식 일정 확인') }}</span>
                    <x-badge variant="cyan" size="xs" pill>{{ trans_auto('운영형 카드') }}</x-badge>
                    <span>{{ trans_auto('운영 시간·예약 가이드 기반 대표 세션') }}</span>
                </div>
            </div>
            <div class="space-y-2">
                @foreach($parties as $party)
                <a href="{{ route('parties.show', $party) }}" class="card flex items-center gap-3.5 p-3.5">
                    <div class="w-12 h-12 rounded-xl overflow-hidden shrink-0 bg-dark-700">
                        @php $thumbnailSrcset = $party->thumbnail_srcset; @endphp
                        <img src="{{ $party->thumbnail_url }}" class="w-full h-full object-cover" loading="lazy" decoding="async"
                             @if($thumbnailSrcset) srcset="{{ $thumbnailSrcset }}" sizes="48px" @endif>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-semibold text-gray-200 truncate">{{ $party->name }}</p>
                        <p class="text-[11px] text-gray-500">{{ $party->club?->name }} · {{ $party->event_date?->format('n/j') }}</p>
                        <div class="mt-1.5 flex flex-wrap gap-1.5">
                            <x-badge :variant="$party->event_card_variant" size="xs" pill>{{ trans_auto($party->event_card_label) }}</x-badge>
                            <x-badge variant="purple" size="xs" pill>{{ trans_auto($party->genre) }}</x-badge>
                        </div>
                        <p class="mt-1.5 line-clamp-2 text-[11px] leading-5 text-gray-400">{{ trans_auto($party->event_card_notice) }}</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- MD 결과 --}}
        @if($mds->isNotEmpty())
        <div>
            <h2 class="text-[15px] font-bold text-white mb-3">MD <span class="text-[12px] text-gray-500 font-normal">{{ $mds->count() }}{{ __('search.results_count') }}</span></h2>
            <div class="space-y-2">
                @foreach($mds as $md)
                <x-md-card :md="$md" />
                @endforeach
            </div>
        </div>
        @endif
    @endif
</div>
@endsection
