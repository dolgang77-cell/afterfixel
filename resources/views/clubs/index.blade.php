@extends('layouts.app')
@section('title', __('nav.club'))
@section('page_type', 'clubs_list')

@section('content')
@php
    $activeFilterCount = collect([
        request('area'),
        request('genre'),
        request('foreigner'),
    ])->filter(fn ($value) => filled($value))->count();

    $mapToggleUrl = route('clubs.index', array_merge(request()->query(), ['view' => 'map']));
    $listToggleUrl = route('clubs.index', request()->except('view'));
    $compareUrl = route('compare.index', ['type' => 'club']);
@endphp

<div class="px-4 py-5 space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-extrabold tracking-tight">{{ __('nav.club') }}</h1>
            <p class="mt-1 text-[11px] text-gray-500">
                {{ trans_auto($viewMode === 'map' ? '현재 필터 결과를 지도와 리스트로 함께 확인할 수 있습니다.' : '지역, 장르, 응답 속도로 빠르게 압축하세요.') }}
            </p>
        </div>
        <span class="text-[11px] text-gray-600 font-medium bg-dark-700/50 px-2.5 py-1 rounded-full">{{ $clubs->count() }}{{ __('filter.count_suffix') }}</span>
    </div>

    <x-list-toolbar routeName="clubs.index"
        :sort-options="$sortOptions"
        :active-sort="$currentSort"
        :view-mode="$viewMode"
        :map-toggle-url="$mapToggleUrl"
        :list-toggle-url="$listToggleUrl"
        :compare-url="$compareUrl"
        :active-compare-count="count($compareItems)"
        :active-filter-count="$activeFilterCount"
        :result-count="$clubs->count()"
        filter-title="클럽 필터">
        <div>
            <p class="mb-2 text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500">{{ trans_auto('추가 조건') }}</p>
            <div class="flex gap-2 overflow-x-auto scrollbar-hide py-0.5">
                <a href="{{ route('clubs.index', request('foreigner') ? request()->except('foreigner') : array_merge(request()->query(), ['foreigner' => 1])) }}"
                   class="shrink-0 rounded-full border px-3.5 py-[7px] text-[11px] font-semibold transition-all active:scale-95 {{ request('foreigner') ? 'bg-blue-500/15 text-blue-300 border-blue-500/20' : 'bg-dark-700/50 text-gray-500 border-white/[0.04] hover:text-gray-300' }}"
                   data-track-event="list_filter_apply"
                   data-track-context="foreigner"
                   data-track-label="외국인 OK"
                   data-track-meta='@json(["param" => "foreigner", "value" => request("foreigner") ? null : 1, "route" => "clubs.index"])'>
                    🌍 {{ trans_auto('외국인 OK') }}
                </a>
            </div>
        </div>
        <div>
            <p class="mb-2 text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500">{{ trans_auto('지역') }}</p>
            <x-filter-chips :items="$areas" param="area" route="clubs.index"
                :allLabel="__('filter.all_area')" :selected="request('area')" :bleed="false" />
        </div>
        <div>
            <p class="mb-2 text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500">{{ trans_auto('장르') }}</p>
            <x-filter-chips :items="$genres" param="genre" route="clubs.index"
                :allLabel="__('filter.all_genre')" color="pink" :selected="request('genre')" :bleed="false" />
        </div>
    </x-list-toolbar>

    <div class="flex items-center justify-between gap-3">
        <p class="text-[11px] text-gray-500">
            {{ trans_auto('현재 필터를 저장해 두면 다음 등록 항목이 조건과 맞을 때 알림으로 다시 받을 수 있습니다.') }}
        </p>
        @include('partials.saved-filter-action', [
            'type' => 'club',
            'filters' => $filterPayload,
            'savedFilter' => $savedFilter,
            'featureAvailable' => $savedFilterFeatureAvailable,
        ])
    </div>

    @if($viewMode === 'map')
        @include('partials.list-map-panel', [
            'title' => '클럽 지도 보기',
            'subtitle' => '현재 필터 결과를 좌표 기준으로 먼저 보고, 아래 리스트로 바로 비교할 수 있습니다.',
            'mapData' => $mapData,
            'targetType' => 'club',
        ])
    @endif

    @if($clubs->isEmpty())
        <x-empty-state :message="__('filter.no_club')"
            :action="route('clubs.index')" :actionLabel="__('filter.reset')" />
    @else
        <div id="list-results" class="space-y-2.5">
            @foreach($clubs as $club)
                @php $isCompared = in_array($club->id, $comparedClubIds, true); @endphp
                <div class="space-y-2">
                    <x-club-card :club="$club" :summary="$cardSummaries[$club->id] ?? null" track-event="list_card_click" track-context="clubs_list" />
                    <form action="{{ route('compare.toggle', ['type' => 'club', 'id' => $club->id]) }}"
                          method="POST"
                          data-track-event="list_compare_add"
                          data-track-trigger="submit"
                          data-track-context="clubs_list"
                          data-track-target-type="club"
                          data-track-target-id="{{ $club->id }}"
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
    'type' => 'club',
    'items' => $compareItems,
    'compareUrl' => $compareUrl,
])
@endsection
