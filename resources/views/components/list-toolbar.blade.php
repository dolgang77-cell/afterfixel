@props([
    'routeName',
    'sortOptions' => [],
    'activeSort' => 'recommended',
    'viewMode' => 'list',
    'mapToggleUrl' => null,
    'listToggleUrl' => null,
    'compareUrl' => null,
    'activeCompareCount' => 0,
    'activeFilterCount' => 0,
    'resultCount' => 0,
    'filterTitle' => '필터',
    'resetLabel' => '초기화',
])

@php
    $currentSortLabel = $sortOptions[$activeSort] ?? reset($sortOptions) ?? '추천순';
    $queryWithoutSort = request()->except('sort');
    $resetUrl = route($routeName, $viewMode === 'map' ? ['view' => 'map'] : []);
@endphp

<div x-data="{ filterOpen: false, sortOpen: false }" class="sticky top-14 z-30 -mx-4 px-4 py-3 bg-dark-950/95 backdrop-blur supports-[backdrop-filter]:bg-dark-950/80">
    <div class="flex gap-2 overflow-x-auto scrollbar-hide">
        <button type="button"
                @click="filterOpen = true"
                class="inline-flex min-h-11 shrink-0 items-center gap-2 rounded-2xl border border-white/[0.08] bg-dark-800 px-4 py-2.5 text-[12px] font-semibold text-gray-100 active:scale-[0.98] transition-transform">
            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h18M6.75 12h10.5M10.5 19.5h3"/></svg>
            {{ trans_auto('필터') }}
            @if($activeFilterCount > 0)
                <span class="rounded-full bg-accent px-1.5 py-0.5 text-[10px] font-bold text-white">{{ $activeFilterCount }}</span>
            @endif
        </button>

        <button type="button"
                @click="sortOpen = true"
                class="inline-flex min-h-11 shrink-0 items-center gap-2 rounded-2xl border border-white/[0.08] bg-dark-800 px-4 py-2.5 text-[12px] font-semibold text-gray-100 active:scale-[0.98] transition-transform">
            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5m-12 5.25h12m-8.25 5.25h8.25"/></svg>
            {{ trans_auto('정렬') }}
            <span class="text-[11px] text-gray-500">{{ trans_auto($currentSortLabel) }}</span>
        </button>

        @if($mapToggleUrl || $listToggleUrl)
            <a href="{{ $viewMode === 'map' ? $listToggleUrl : $mapToggleUrl }}"
               class="inline-flex min-h-11 shrink-0 items-center gap-2 rounded-2xl border border-white/[0.08] bg-dark-800 px-4 py-2.5 text-[12px] font-semibold text-gray-100 active:scale-[0.98] transition-transform"
               data-track-event="list_map_toggle"
               data-track-context="{{ $routeName }}"
               data-track-label="{{ $viewMode === 'map' ? '리스트 보기' : '지도 보기' }}">
                <svg class="h-4 w-4 text-cyan-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z"/></svg>
                {{ trans_auto($viewMode === 'map' ? '리스트' : '지도') }}
            </a>
        @endif

        @if($compareUrl)
            <a href="{{ $compareUrl }}"
               class="inline-flex min-h-11 shrink-0 items-center gap-2 rounded-2xl border border-white/[0.08] bg-dark-800 px-4 py-2.5 text-[12px] font-semibold text-gray-100 active:scale-[0.98] transition-transform"
               data-track-event="list_compare_open"
               data-track-context="{{ $routeName }}"
               data-track-label="비교 {{ $activeCompareCount }}">
                <svg class="h-4 w-4 text-violet-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 5.25v13.5m9-13.5v13.5M3.75 8.25h7.5m-7.5 7.5h7.5m1.5-7.5h7.5m-7.5 7.5h7.5"/></svg>
                {{ trans_auto('비교') }}
                <span class="rounded-full bg-violet-500/15 px-1.5 py-0.5 text-[10px] font-bold text-violet-200">{{ $activeCompareCount }}</span>
            </a>
        @endif

        <div class="inline-flex min-h-11 shrink-0 items-center gap-2 rounded-2xl border border-white/[0.08] bg-dark-800 px-4 py-2.5 text-[12px] font-semibold text-gray-100">
            <svg class="h-4 w-4 text-pink-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 0l3.75-3.75m-3.75 3.75l3.75 3.75"/></svg>
            {{ number_format($resultCount) }}
            <span class="text-[11px] text-gray-500">{{ trans_auto('결과') }}</span>
        </div>
    </div>

    <div class="mt-2 flex items-center justify-between px-1 text-[11px] text-gray-500">
        <p>{{ trans_auto('필터와 정렬을 유지한 채 지도와 리스트를 전환할 수 있습니다.') }}</p>
        <p>{{ trans_auto($viewMode === 'map' ? '지도 보기' : $currentSortLabel) }}</p>
    </div>

    <template x-teleport="body">
        <div x-cloak x-show="filterOpen || sortOpen" class="fixed inset-0 z-[70] bg-black/60" @click="filterOpen = false; sortOpen = false"></div>
    </template>

    <template x-teleport="body">
        <div x-cloak x-show="filterOpen" x-transition class="fixed inset-0 z-[80] flex items-center justify-center px-4 py-6">
            <div class="w-full max-w-md rounded-[28px] border border-white/[0.06] bg-dark-900 shadow-card max-h-[calc(100vh-4rem)] overflow-hidden">
                <div class="px-4 pt-5 pb-4">
                    <div class="mx-auto mb-4 h-1.5 w-12 rounded-full bg-white/10"></div>
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-500">{{ trans_auto('빠른 압축') }}</p>
                            <h3 class="mt-1 text-[17px] font-bold text-white">{{ trans_auto($filterTitle) }}</h3>
                        </div>
                        <button type="button"
                                @click="filterOpen = false"
                                class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/[0.08] text-gray-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                <div class="overflow-y-auto px-4 pb-4" style="max-height: calc(100vh - 15rem);">
                    <div class="space-y-4">
                        {{ $slot }}
                    </div>
                </div>

                <div class="border-t border-white/[0.06] bg-dark-900/95 px-4 pt-4 pb-4">
                    <div class="grid grid-cols-3 gap-2">
                        <a href="{{ $resetUrl }}"
                           class="rounded-2xl border border-white/[0.08] bg-dark-800 px-4 py-3 text-center text-[12px] font-semibold text-gray-300">
                            {{ trans_auto($resetLabel) }}
                        </a>
                        <button type="button"
                                @click="filterOpen = false"
                                class="rounded-2xl border border-white/[0.08] bg-dark-800 px-4 py-3 text-[12px] font-semibold text-gray-300">
                            {{ trans_auto('닫기') }}
                        </button>
                        <button type="button"
                                @click="filterOpen = false"
                                class="rounded-2xl btn-primary px-4 py-3 text-[12px] font-semibold text-white">
                            {{ trans_auto('결과 보기') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <template x-teleport="body">
        <div x-cloak x-show="sortOpen" x-transition class="fixed inset-0 z-[80] flex items-center justify-center px-4 py-6">
            <div class="w-full max-w-sm rounded-[28px] border border-white/[0.06] bg-dark-900 shadow-card">
                <div class="px-4 pt-5 pb-4">
                    <div class="mx-auto mb-4 h-1.5 w-12 rounded-full bg-white/10"></div>
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-500">{{ trans_auto('리스트 정렬') }}</p>
                            <h3 class="mt-1 text-[17px] font-bold text-white">{{ trans_auto('정렬 선택') }}</h3>
                        </div>
                        <button type="button"
                                @click="sortOpen = false"
                                class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/[0.08] text-gray-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                <div class="px-4 pb-4">
                    <div class="space-y-2">
                        @foreach($sortOptions as $value => $label)
                            <form method="GET" action="{{ route($routeName) }}" data-track-event="list_sort_change" data-track-trigger="submit" data-track-context="{{ $routeName }}" data-track-label="{{ trans_auto($label) }}" data-track-meta='@json(["sort" => $value])'>
                                @foreach($queryWithoutSort as $key => $item)
                                    <input type="hidden" name="{{ $key }}" value="{{ $item }}">
                                @endforeach
                                <button type="submit"
                                        name="sort"
                                        value="{{ $value }}"
                                        class="flex w-full items-center justify-between rounded-2xl border px-4 py-3 text-left text-[13px] font-semibold transition-colors {{ $activeSort === $value ? 'border-accent bg-accent/10 text-white' : 'border-white/[0.08] bg-dark-800 text-gray-300' }}">
                                    <span>{{ trans_auto($label) }}</span>
                                    @if($activeSort === $value)
                                        <svg class="h-4 w-4 text-accent-light" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    @endif
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>

                <div class="border-t border-white/[0.06] bg-dark-900/95 px-4 pt-4 pb-4">
                    <button type="button"
                            @click="sortOpen = false"
                            class="w-full rounded-2xl border border-white/[0.08] bg-dark-800 px-4 py-3 text-[12px] font-semibold text-gray-300">
                        {{ trans_auto('닫기') }}
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
