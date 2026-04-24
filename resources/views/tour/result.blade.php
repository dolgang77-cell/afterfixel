@extends('layouts.app')
@section('title', __('tour.result_title'))

@section('content')
<div class="px-4 py-5 space-y-5" x-data="{ activeTab: 0 }">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-extrabold tracking-tight">{{ __('tour.result_title') }}</h1>
            <p class="text-[11px] text-gray-500 mt-1 flex items-center gap-1.5">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                {{ $validated['area'] }} · {{ $validated['time'] }} 출발 · {{ number_format($validated['budget']) }}원
                @if(!empty($validated['genre']))· {{ implode(', ', $validated['genre']) }}@endif
            </p>
        </div>
        <a href="{{ route('tour.index') }}" class="btn-secondary text-[11px] px-3 py-1.5 rounded-full font-semibold">{{ __('tour.reset') }}</a>
    </div>

    {{-- ═══ 조건 완화 / 메시지 ═══ --}}
    @if(!empty($results['message']))
        <x-alert variant="info">{{ $results['message'] }}</x-alert>
    @endif
    @if(!empty($results['suggestion']))
        <x-alert variant="warning">{{ $results['suggestion'] }}</x-alert>
    @endif

    {{-- ═══ 결과 없음 ═══ --}}
    @if(empty($results['routes']))
        <x-empty-state icon="😢" message="{{ __('tour.no_result') }}">
            @if(!empty($results['suggestion']))
                <p class="text-[13px] text-accent mt-2">💡 {{ $results['suggestion'] }}</p>
            @endif
            <a href="{{ route('tour.index') }}" class="btn-secondary inline-block mt-4 px-5 py-2.5 rounded-2xl text-[13px] font-semibold">
                {{ __('tour.change_condition') }}
            </a>
        </x-empty-state>
    @else

        {{-- ═══ 루트 탭 (다중 루트) ═══ --}}
        @if(count($results['routes']) > 1)
        <div class="flex bg-dark-800 p-1 rounded-2xl border border-white/[0.03]">
            @foreach($results['routes'] as $idx => $r)
            <button @click="activeTab = {{ $idx }}"
                    :class="activeTab === {{ $idx }} ? 'gradient-accent text-white shadow-glow-sm' : 'text-gray-500'"
                    class="flex-1 text-center py-2.5 rounded-xl text-[11px] font-semibold transition-all">
                {{ $r['label'] ?? '루트 '.($idx+1) }}
            </button>
            @endforeach
        </div>
        @endif

        @foreach($results['routes'] as $routeIdx => $route)
        <div x-show="activeTab === {{ $routeIdx }}" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-5">

            {{-- ═══ 루트 설명 ═══ --}}
            @if(!empty($route['summary']))
            <div class="card p-4">
                <p class="text-[12px] text-gray-400 leading-[1.7]">📝 {{ $route['summary'] }}</p>
            </div>
            @endif

            {{-- ═══ Summary Card ═══ --}}
            <div class="relative rounded-3xl overflow-hidden">
                <div class="absolute inset-0 gradient-accent opacity-[0.08]"></div>
                <div class="relative border border-purple-500/15 rounded-3xl p-5">
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <p class="text-[24px] font-extrabold text-accent leading-none">{{ $route['total_stops'] }}</p>
                            <p class="text-[10px] text-gray-500 mt-1 font-medium">스탑</p>
                        </div>
                        <div>
                            <p class="text-[20px] font-extrabold text-pink-400 leading-none">{{ number_format($route['total_cost']) }}<span class="text-[11px]">원</span></p>
                            <p class="text-[10px] text-gray-500 mt-1 font-medium">{{ __('club.entry_fee_label') }}</p>
                        </div>
                        <div>
                            <p class="text-[20px] font-extrabold text-blue-400 leading-none">{{ $route['total_travel_minutes'] }}<span class="text-[11px]">분</span></p>
                            <p class="text-[10px] text-gray-500 mt-1 font-medium">{{ __('club.travel_time') }}</p>
                        </div>
                    </div>
                    <div class="my-4 h-px bg-gradient-to-r from-transparent via-purple-500/20 to-transparent"></div>
                    <div class="grid grid-cols-4 gap-3 text-center">
                        <div>
                            <p class="text-[14px] font-bold text-emerald-400">{{ $route['probability_score'] }}%</p>
                            <p class="text-[9px] text-gray-500 font-medium">만남 확률</p>
                        </div>
                        <div>
                            <p class="text-[14px] font-bold text-orange-400">{{ number_format($route['calorie_score']) }}</p>
                            <p class="text-[9px] text-gray-500 font-medium">칼로리</p>
                        </div>
                        <div>
                            <p class="text-[14px] font-bold text-amber-400">{{ number_format($route['drink_budget']) }}<span class="text-[9px]">원</span></p>
                            <p class="text-[9px] text-gray-500 font-medium">{{ __('club.drink_budget') }}</p>
                        </div>
                        <div>
                            <p class="text-[14px] font-bold text-cyan-400">~{{ number_format($route['total_taxi_fare'] ?? 0) }}<span class="text-[9px]">원</span></p>
                            <p class="text-[9px] text-gray-500 font-medium">{{ __('club.taxi_fare') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══ 경고 배너 ═══ --}}
            @if(!empty($route['warning_summary']))
                @foreach($route['warning_summary'] as $warnText)
                    <x-alert variant="warning">{{ $warnText }}</x-alert>
                @endforeach
            @endif

            {{-- ═══ Route Timeline ═══ --}}
            <div class="space-y-0">
                @foreach($route['stops'] as $index => $stop)
                <div class="relative pl-9">
                    @if($index < count($route['stops'])-1)
                        <div class="absolute left-[14px] top-8 bottom-0 w-[2px] bg-gradient-to-b from-accent/40 to-accent/10"></div>
                    @endif

                    <div class="absolute left-1 top-4 w-[22px] h-[22px] rounded-full gradient-accent flex items-center justify-center shadow-glow-sm z-10">
                        <span class="text-[10px] font-extrabold text-white">{{ $stop['order'] }}</span>
                    </div>

                    @if($stop['travel_minutes'] > 0)
                    <div class="pb-2 flex items-center gap-2">
                        <span class="text-[10px] text-gray-600 bg-dark-700/50 px-2.5 py-1 rounded-lg border border-white/[0.03] font-medium">
                            🚕 {{ $stop['travel_minutes'] }}분
                            @if(!empty($stop['taxi_fare']))· ~{{ number_format($stop['taxi_fare']) }}원@endif
                        </span>
                    </div>
                    @endif

                    {{-- Stop Card --}}
                    <div class="card p-4 mb-3">
                        <a href="{{ route('clubs.show', $stop['club']) }}" class="flex gap-3.5 group">
                            <div class="w-[56px] h-[56px] rounded-2xl overflow-hidden shrink-0 ring-1 ring-white/[0.06]">
                                <img src="{{ $stop['club']->thumbnail_url }}" alt="{{ $stop['club']->name }}" class="w-full h-full object-cover" loading="lazy">
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <h3 class="text-[13px] font-bold text-gray-100 truncate">{{ $stop['club']->name }}</h3>
                                    <span class="text-[11px] text-accent font-bold shrink-0">{{ $stop['arrival_time'] }}</span>
                                </div>
                                <p class="text-[11px] text-gray-500 mt-0.5">{{ trans_auto($stop['club']->area) }} · {{ trans_auto($stop['club']->genre) }}</p>
                                <div class="flex items-center gap-1.5 mt-2">
                                    <x-badge size="xs">{{ number_format($stop['cost']) }}원</x-badge>
                                    <x-badge size="xs">{{ $stop['stay_minutes'] }}분</x-badge>
                                    @if(!$stop['club']->foreigner_allowed && !empty($validated['foreigner_mode']))
                                        <x-badge variant="red" size="xs">{{ __('club.foreigner_restricted') }}</x-badge>
                                    @endif
                                </div>
                            </div>
                        </a>

                        {{-- 스탑별 경고 --}}
                        @if(!empty($stop['warnings']))
                            <div class="mt-2.5 space-y-1">
                                @foreach($stop['warnings'] as $w)
                                    <div class="flex items-start gap-1.5 text-[10px] {{ $w['severity'] === 'high' ? 'text-red-400' : 'text-amber-400' }}">
                                        <span class="shrink-0 mt-[1px]">{{ $w['severity'] === 'high' ? '🚨' : '⚠️' }}</span>
                                        <span>{{ $w['message'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- 추천 이유 (자연어) --}}
                        @if(!empty($stop['explanation']))
                            <div class="mt-2.5 pt-2.5 border-t border-white/[0.03]">
                                <p class="text-[11px] text-gray-400 leading-[1.6]">💡 {{ $stop['explanation'] }}</p>
                            </div>
                        @endif

                        {{-- 점수 breakdown 태그 --}}
                        <div class="mt-2 flex flex-wrap gap-1">
                            @foreach($stop['reasons'] ?? [] as $reason)
                                <span class="text-[9px] font-medium text-purple-400/70 bg-purple-500/[0.06] px-2 py-[3px] rounded-md">{{ $reason }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

        </div>
        @endforeach

        <a href="{{ route('tour.index') }}"
           class="btn-primary block w-full text-center py-3.5 rounded-2xl font-bold text-[14px] text-white">
            {{ __('tour.retry') }}
        </a>
    @endif
</div>
@endsection
