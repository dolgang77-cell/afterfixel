@extends('layouts.app')
@section('title', __('pref.title'))

@section('content')
<div class="px-4 py-6 space-y-5">
    <div class="flex items-center gap-3 mb-2">
        <a href="{{ route('my.index') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-dark-700/60 text-gray-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        </a>
        <h1 class="text-[20px] font-extrabold text-white">{{ __('pref.title') }}</h1>
    </div>

    <p class="text-[12px] text-gray-500 -mt-2">{{ __('pref.desc') }}</p>

    <form method="POST" action="{{ route('my.preferences.update') }}" class="space-y-5">
        @csrf

        {{-- 관심 지역 --}}
        <div class="card p-4">
            <h3 class="text-[13px] font-bold text-gray-300 mb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg gradient-pink flex items-center justify-center text-[11px]">📍</span>
                {{ __('pref.area') }}
            </h3>
            <div class="flex flex-wrap gap-2">
                @foreach(\App\Models\Club::$areas as $area)
                    <label class="cursor-pointer">
                        <input type="checkbox" name="preferred_areas[]" value="{{ $area }}"
                               {{ in_array($area, $pref->preferred_areas ?? []) ? 'checked' : '' }}
                               class="sr-only peer">
                        <span class="block px-3 py-1.5 rounded-full text-[12px] font-medium border border-white/[0.08] bg-dark-700/60 text-gray-400 peer-checked:border-pink-500/50 peer-checked:bg-pink-500/10 peer-checked:text-pink-400 transition-all">
                            {{ $area }}
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- 관심 장르 --}}
        <div class="card p-4">
            <h3 class="text-[13px] font-bold text-gray-300 mb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg gradient-accent flex items-center justify-center text-[11px]">🎵</span>
                {{ __('pref.genre') }}
            </h3>
            <div class="flex flex-wrap gap-2">
                @foreach(\App\Models\Club::$genres as $genre)
                    <label class="cursor-pointer">
                        <input type="checkbox" name="preferred_genres[]" value="{{ $genre }}"
                               {{ in_array($genre, $pref->preferred_genres ?? []) ? 'checked' : '' }}
                               class="sr-only peer">
                        <span class="block px-3 py-1.5 rounded-full text-[12px] font-medium border border-white/[0.08] bg-dark-700/60 text-gray-400 peer-checked:border-accent/50 peer-checked:bg-accent/10 peer-checked:text-accent transition-all">
                            {{ $genre }}
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- 예산 --}}
        <div class="card p-4">
            <h3 class="text-[13px] font-bold text-gray-300 mb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg gradient-purple flex items-center justify-center text-[11px]">💰</span>
                {{ __('pref.budget') }}
            </h3>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-[11px] text-gray-500 mb-1 block">{{ __('pref.min') }}</label>
                    <input type="number" name="budget_min" value="{{ old('budget_min', $pref->budget_min) }}"
                           placeholder="0" min="0" step="10000"
                           class="w-full px-3 py-2 bg-dark-700/60 border border-white/[0.06] rounded-xl text-[13px] text-gray-200 placeholder-gray-600">
                </div>
                <div>
                    <label class="text-[11px] text-gray-500 mb-1 block">{{ __('pref.max') }}</label>
                    <input type="number" name="budget_max" value="{{ old('budget_max', $pref->budget_max) }}"
                           placeholder="100000" min="0" step="10000"
                           class="w-full px-3 py-2 bg-dark-700/60 border border-white/[0.06] rounded-xl text-[13px] text-gray-200 placeholder-gray-600">
                </div>
            </div>
        </div>

        {{-- 외국인 모드 --}}
        <div class="card p-4 flex items-center justify-between">
            <div>
                <p class="text-[13px] font-semibold text-gray-200">{{ __('pref.foreigner') }}</p>
                <p class="text-[11px] text-gray-500">{{ __('pref.foreigner_desc') }}</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="hidden" name="foreigner_mode" value="0">
                <input type="checkbox" name="foreigner_mode" value="1" class="sr-only peer"
                       {{ $pref->foreigner_mode ? 'checked' : '' }}>
                <div class="w-11 h-6 bg-dark-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
            </label>
        </div>

        <button type="submit" class="btn-primary w-full py-3.5 rounded-2xl font-bold text-[14px] text-white">
            {{ __('pref.save') }}
        </button>
    </form>
</div>
@endsection
