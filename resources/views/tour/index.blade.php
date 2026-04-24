@extends('layouts.app')
@section('title', __('tour.title'))

@section('content')
<div class="px-4 py-5 space-y-6">
    @php
        $selectedGenres = collect(old('genre', $pref?->preferred_genres ?? []))
            ->filter()
            ->values()
            ->all();
        $foreignerModeEnabled = (bool) old('foreigner_mode', $pref?->foreigner_mode ?? false);
    @endphp
    {{-- Header --}}
    <div class="text-center pt-2">
        <div class="w-14 h-14 mx-auto mb-4 rounded-3xl gradient-accent flex items-center justify-center shadow-glow animate-float">
            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m0-8.25a6 6 0 0112 0v8.25M9 6.75a6 6 0 00-6 6v.75"/></svg>
        </div>
        <h1 class="text-[22px] font-extrabold tracking-tight">{{ __('tour.hero_title') }}<br><span class="gradient-text">{{ __('tour.hero_sub') }}</span></h1>
        <p class="text-[13px] text-gray-500 mt-2.5">{{ __('tour.hero_desc') }}</p>
    </div>

    {{-- Form --}}
    <form action="{{ route('tour.recommend') }}" method="POST" class="space-y-4" x-data="{ budget: 100000, radius: 3, foreignerMode: {{ $foreignerModeEnabled ? 'true' : 'false' }}, selectedGenres: {{ \Illuminate\Support\Js::from($selectedGenres) }}, get radiusValue() { return this.radius >= 11 ? 0 : this.radius }, get budgetValue() { return this.budget >= 310000 ? 0 : this.budget } }">
        @csrf

        {{-- Area --}}
        <div class="card p-4">
            <label class="text-[13px] font-bold text-gray-300 mb-3 flex items-center gap-2 block">
                <span class="w-6 h-6 rounded-lg bg-purple-500/15 flex items-center justify-center text-[11px]">📍</span>
                {{ __('tour.area') }}
            </label>
            <div class="grid grid-cols-4 gap-2">
                @foreach($areas as $a)
                <label class="cursor-pointer">
                    <input type="radio" name="area" value="{{ $a }}" class="hidden peer" {{ request('area') === $a || ($loop->first && !request('area')) ? 'checked' : '' }}>
                    <div class="text-center py-2.5 rounded-xl text-[11px] font-semibold border border-white/[0.04] bg-dark-700/40 text-gray-500
                                peer-checked:gradient-accent peer-checked:text-white peer-checked:border-transparent peer-checked:shadow-glow-sm
                                transition-all active:scale-95">
                        {{ trans_auto($a) }}
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        {{-- Time --}}
        <div class="card p-4">
            <label class="text-[13px] font-bold text-gray-300 mb-3 flex items-center gap-2 block">
                <span class="w-6 h-6 rounded-lg bg-pink-500/15 flex items-center justify-center text-[11px]">🕐</span>
                {{ __('tour.time') }}
            </label>
            <div class="grid grid-cols-3 gap-2">
                @foreach(['21:00' => '21:00', '22:00' => '22:00', '23:00' => '23:00', '00:00' => __('tour.midnight'), '01:00' => __('tour.dawn').' 1:00', '02:00' => __('tour.dawn').' 2:00'] as $val => $label)
                <label class="cursor-pointer">
                    <input type="radio" name="time" value="{{ $val }}" class="hidden peer" {{ $val === '22:00' ? 'checked' : '' }}>
                    <div class="text-center py-2.5 rounded-xl text-[11px] font-semibold border border-white/[0.04] bg-dark-700/40 text-gray-500
                                peer-checked:gradient-accent peer-checked:text-white peer-checked:border-transparent peer-checked:shadow-glow-sm
                                transition-all active:scale-95">
                        {{ $label }}
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        {{-- Genre --}}
        <div class="card p-4">
            <label class="text-[13px] font-bold text-gray-300 mb-3 flex items-center gap-2 block">
                <span class="w-6 h-6 rounded-lg bg-blue-500/15 flex items-center justify-center text-[11px]">🎵</span>
                {{ __('tour.genre') }}
                <span class="text-[10px] text-gray-600 font-normal">({{ __('community.optional') }} · 여러 개 선택 가능)</span>
            </label>
            <p class="text-[11px] text-gray-500 mb-3">선택하지 않으면 모든 장르를 기준으로 추천합니다.</p>
            <div class="flex flex-wrap gap-2">
                @foreach($genres as $g)
                <label class="cursor-pointer">
                    <input type="checkbox" name="genre[]" value="{{ $g }}" class="hidden peer" x-model="selectedGenres" {{ in_array($g, $selectedGenres, true) ? 'checked' : '' }}>
                    <div class="px-3.5 py-2 rounded-xl text-[11px] font-semibold border border-white/[0.04] bg-dark-700/40 text-gray-500
                                peer-checked:bg-pink-500/15 peer-checked:text-pink-400 peer-checked:border-pink-500/20
                                transition-all active:scale-95">
                        {{ trans_auto($g) }}
                    </div>
                </label>
                @endforeach
            </div>
            <p class="mt-3 text-[11px] text-gray-500">선택된 장르 수: <span class="font-semibold text-gray-300" x-text="selectedGenres.length"></span></p>
        </div>

        {{-- Radius --}}
        <div class="card p-4">
            <label class="text-[13px] font-bold text-gray-300 mb-3 flex items-center gap-2 block">
                <span class="w-6 h-6 rounded-lg bg-cyan-500/15 flex items-center justify-center text-[11px]">📏</span>
                {{ __('tour.radius') }}
            </label>
            <input type="hidden" name="radius" :value="radiusValue">
            <div class="relative">
                <input type="range" min="1" max="11" step="1" x-model="radius"
                       class="w-full h-1.5 rounded-full appearance-none cursor-pointer bg-dark-600
                              [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:w-5 [&::-webkit-slider-thumb]:h-5
                              [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-white [&::-webkit-slider-thumb]:shadow-glow
                              [&::-webkit-slider-thumb]:border-2 [&::-webkit-slider-thumb]:border-accent">
            </div>
            <div class="flex justify-between mt-3">
                <span class="text-[10px] text-gray-600">1km</span>
                <span class="text-[15px] font-bold gradient-text-simple" x-text="radius >= 11 ? '{{ __('tour.unlimited') }}' : radius + 'km'"></span>
                <span class="text-[10px] text-gray-600">{{ __('tour.unlimited') }}</span>
            </div>
        </div>

        {{-- Budget --}}
        <div class="card p-4">
            <label class="text-[13px] font-bold text-gray-300 mb-3 flex items-center gap-2 block">
                <span class="w-6 h-6 rounded-lg bg-amber-500/15 flex items-center justify-center text-[11px]">💰</span>
                {{ __('tour.budget') }}
            </label>
            <input type="hidden" name="budget" :value="budgetValue">
            <div class="relative">
                <input type="range" min="30000" max="310000" step="10000" x-model="budget"
                       class="w-full h-1.5 rounded-full appearance-none cursor-pointer bg-dark-600
                              [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:w-5 [&::-webkit-slider-thumb]:h-5
                              [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-white [&::-webkit-slider-thumb]:shadow-glow
                              [&::-webkit-slider-thumb]:border-2 [&::-webkit-slider-thumb]:border-accent">
            </div>
            <div class="flex justify-between mt-3">
                <span class="text-[10px] text-gray-600">3{{ __('tour.won') }}</span>
                <span class="text-[15px] font-bold gradient-text-simple" x-text="budget >= 310000 ? '{{ __('tour.unlimited') }}' : new Intl.NumberFormat('ko-KR').format(budget) + '{{ __('tour.won_suffix') }}'"></span>
                <span class="text-[10px] text-gray-600">{{ __('tour.unlimited') }}</span>
            </div>
        </div>

        {{-- Foreigner mode --}}
        <label class="card flex items-center gap-4 p-4 cursor-pointer transition-all"
               :class="foreignerMode ? 'border-cyan-400/30 bg-cyan-500/[0.08] shadow-[0_0_0_1px_rgba(34,211,238,0.08)]' : 'border-white/[0.03] bg-transparent'">
            <div class="relative">
                <input type="checkbox" name="foreigner_mode" value="1" class="sr-only peer" x-model="foreignerMode" {{ $foreignerModeEnabled ? 'checked' : '' }}>
                <div class="w-10 h-6 bg-dark-600 rounded-full peer-checked:gradient-accent transition-all"></div>
                <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full transition-transform peer-checked:translate-x-4 shadow-md"></div>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[13px] font-semibold text-gray-200">🌍 {{ __('tour.foreigner_mode') }}</p>
                <p class="text-[11px] text-gray-500">{{ __('tour.foreigner_desc') }}</p>
            </div>
            <div class="shrink-0">
                <span class="inline-flex items-center rounded-full px-3 py-1 text-[11px] font-bold transition-all"
                      :class="foreignerMode ? 'bg-cyan-400/15 text-cyan-300 border border-cyan-400/30' : 'bg-white/5 text-gray-500 border border-white/[0.06]'"
                      x-text="foreignerMode ? 'ON' : 'OFF'"></span>
            </div>
        </label>

        {{-- Submit --}}
        <button type="submit"
                class="btn-primary w-full py-4 rounded-2xl font-bold text-[14px] text-white flex items-center justify-center gap-2.5">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
            {{ __('tour.generate') }}
        </button>
    </form>
</div>
@endsection
