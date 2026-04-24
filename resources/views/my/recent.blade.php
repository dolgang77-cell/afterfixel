@extends('layouts.app')
@section('title', __('my.recent_title'))

@section('content')
<div class="px-4 py-6">
    <div class="flex items-center gap-3 mb-5">
        <a href="{{ route('my.index') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-dark-700/60 text-gray-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        </a>
        <h1 class="text-[20px] font-extrabold text-white">{{ __('my.recent_title') }}</h1>
    </div>

    {{-- 탭 --}}
    <div class="flex gap-2 mb-5">
        @foreach(['all' => __('filter.all'), 'party' => __('nav.party'), 'club' => __('nav.club'), 'tour' => __('tour.tour_label')] as $key => $label)
            <a href="?tab={{ $key }}"
               class="px-3.5 py-1.5 rounded-full text-[12px] font-medium transition-all {{ $tab === $key ? 'gradient-accent text-white shadow-glow-sm' : 'bg-dark-700/60 text-gray-400 border border-white/[0.06]' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="space-y-2.5">
        @forelse($views as $rv)
            @if($rv->target)
                @if($rv->type === 'party')
                    <a href="{{ route('parties.show', $rv->target) }}" class="card flex items-center gap-3.5 p-3.5">
                        <div class="w-12 h-12 rounded-xl overflow-hidden shrink-0 bg-dark-700">
                            <img src="{{ $rv->target->thumbnail_url }}" class="w-full h-full object-cover" alt="">
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[13px] font-semibold text-gray-200 truncate">{{ $rv->target->name }}</p>
                            <p class="text-[11px] text-gray-500">{{ __('nav.party') }} · {{ $rv->target->club?->area ?? '' }}</p>
                        </div>
                        <span class="text-[10px] text-gray-600 shrink-0">{{ $rv->viewed_at->diffForHumans() }}</span>
                    </a>
                @elseif($rv->type === 'club')
                    <a href="{{ route('clubs.show', $rv->target) }}" class="card flex items-center gap-3.5 p-3.5">
                        <div class="w-12 h-12 rounded-xl overflow-hidden shrink-0 bg-dark-700">
                            <img src="{{ $rv->target->thumbnail_url }}" class="w-full h-full object-cover" alt="">
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[13px] font-semibold text-gray-200 truncate">{{ $rv->target->name }}</p>
                            <p class="text-[11px] text-gray-500">{{ __('nav.club') }} · {{ $rv->target->area }}</p>
                        </div>
                        <span class="text-[10px] text-gray-600 shrink-0">{{ $rv->viewed_at->diffForHumans() }}</span>
                    </a>
                @elseif($rv->type === 'tour')
                    <div class="card p-3.5 flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-xl gradient-accent/20 flex items-center justify-center shrink-0 text-[18px]">🗺️</div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[13px] font-semibold text-gray-200">{{ $rv->target->input_condition['area'] ?? '' }} {{ __('tour.tour_label') }}</p>
                            <p class="text-[11px] text-gray-500">{{ number_format($rv->target->total_cost ?? 0) }}{{ __('tour.won_suffix') }}</p>
                        </div>
                        <span class="text-[10px] text-gray-600 shrink-0">{{ $rv->viewed_at->diffForHumans() }}</span>
                    </div>
                @endif
            @endif
        @empty
            <x-empty-state :message="__('my.recent_empty')" />
        @endforelse
    </div>
</div>
@endsection
