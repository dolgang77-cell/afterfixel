@extends('layouts.app')
@section('title', __('nav.party'))
@section('page_type', 'parties_list')

@section('content')
@php
    $dateItems = [
        today()->toDateString() => __('filter.today'),
        today()->addDay()->toDateString() => __('filter.tomorrow'),
    ];

    $activeFilterCount = collect([
        request('date'),
        request('area'),
        request('genre'),
    ])->filter(fn ($value) => filled($value))->count();

    $mapToggleUrl = route('parties.index', array_merge(request()->query(), ['view' => 'map']));
    $listToggleUrl = route('parties.index', request()->except('view'));
    $compareUrl = route('compare.index', ['type' => 'party']);
@endphp

<div class="px-4 py-5 space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-extrabold tracking-tight">{{ __('nav.party') }}</h1>
            <p class="mt-1 text-[11px] text-gray-500">
                {{ trans_auto($viewMode === 'map' ? '현재 필터 결과를 지도와 리스트로 함께 확인할 수 있습니다.' : '날짜와 지역을 먼저 좁히고 빠르게 비교하세요.') }}
            </p>
        </div>
        <span class="text-[11px] text-gray-600 font-medium bg-dark-700/50 px-2.5 py-1 rounded-full">{{ $parties->count() }}{{ __('filter.count_suffix2') }}</span>
    </div>

    <x-list-toolbar routeName="parties.index"
        :sort-options="$sortOptions"
        :active-sort="$currentSort"
        :view-mode="$viewMode"
        :map-toggle-url="$mapToggleUrl"
        :list-toggle-url="$listToggleUrl"
        :compare-url="$compareUrl"
        :active-compare-count="count($compareItems)"
        :active-filter-count="$activeFilterCount"
        :result-count="$parties->count()"
        filter-title="파티 필터">
        <div>
            <p class="mb-2 text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500">{{ trans_auto('날짜') }}</p>
            <x-filter-chips :items="$dateItems" param="date" route="parties.index"
                :allLabel="__('filter.all')" :selected="request('date')" :bleed="false" />
        </div>
        <div>
            <p class="mb-2 text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500">{{ trans_auto('지역') }}</p>
            <x-filter-chips :items="$areas" param="area" route="parties.index"
                :allLabel="__('filter.all_area')" color="pink" :selected="request('area')" :bleed="false" />
        </div>
        <div>
            <p class="mb-2 text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500">{{ trans_auto('장르') }}</p>
            <x-filter-chips :items="$genres" param="genre" route="parties.index"
                :allLabel="__('filter.all_genre')" :selected="request('genre')" :bleed="false" />
        </div>
    </x-list-toolbar>

    <div class="flex items-center justify-between gap-3">
        <p class="text-[11px] text-gray-500">
            {{ trans_auto('현재 조건을 저장해 두면 새 파티나 장소가 맞춰 등록될 때 알림으로 다시 받을 수 있습니다.') }}
        </p>
        @include('partials.saved-filter-action', [
            'type' => 'party',
            'filters' => $filterPayload,
            'savedFilter' => $savedFilter,
            'featureAvailable' => $savedFilterFeatureAvailable,
        ])
    </div>

    <div class="flex flex-wrap items-center gap-2 text-[11px] text-gray-400">
        <span>{{ trans_auto('카드 구분') }}</span>
        <x-badge variant="green" size="xs" pill>{{ trans_auto('실이벤트') }}</x-badge>
        <span>{{ trans_auto('외부 공지·공식 일정 확인') }}</span>
        <x-badge variant="cyan" size="xs" pill>{{ trans_auto('운영형 카드') }}</x-badge>
        <span>{{ trans_auto('운영 시간·예약 가이드 기반 대표 세션') }}</span>
    </div>

    @if($viewMode === 'map')
        @include('partials.list-map-panel', [
            'title' => '파티 지도 보기',
            'subtitle' => '현재 날짜와 지역 조건을 유지한 채 파티 위치를 먼저 확인할 수 있습니다.',
            'mapData' => $mapData,
            'targetType' => 'party',
        ])
    @endif

    @if($parties->isEmpty())
        <x-empty-state :message="__('filter.no_party')"
            :action="route('parties.index')" :actionLabel="__('filter.reset')" />
    @else
        <div id="list-results" class="space-y-2.5">
            @foreach($parties as $party)
                @php $isCompared = in_array($party->id, $comparedPartyIds, true); @endphp
                <div class="space-y-2">
                    <x-party-card :party="$party" :summary="$cardSummaries[$party->id] ?? null" track-event="list_card_click" track-context="parties_list" />
                    <form action="{{ route('compare.toggle', ['type' => 'party', 'id' => $party->id]) }}"
                          method="POST"
                          data-track-event="list_compare_add"
                          data-track-trigger="submit"
                          data-track-context="parties_list"
                          data-track-target-type="party"
                          data-track-target-id="{{ $party->id }}"
                          data-track-meta='@json(["action" => $isCompared ? "remove" : "add"])'>
                        @csrf
                        <button type="submit"
                                class="flex w-full items-center justify-center gap-2 rounded-2xl border px-4 py-3 text-[12px] font-semibold {{ $isCompared ? 'border-violet-500/20 bg-violet-500/10 text-violet-200' : 'border-white/[0.06] bg-dark-800 text-gray-300' }}">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 5.25v13.5m9-13.5v13.5M3.75 8.25h7.5m-7.5 7.5h7.5m1.5-7.5h7.5m-7.5 7.5h7.5"/></svg>
                            {{ $isCompared ? trans_auto('비교함에서 제거') : trans_auto('비교 추가') }}
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
</div>

@include('partials.compare-tray', [
    'type' => 'party',
    'items' => $compareItems,
    'compareUrl' => $compareUrl,
])
@endsection
