@extends('layouts.app')
@section('title', __('community.title'))

@section('content')
<div class="px-4 py-5 space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-extrabold tracking-tight">{{ __('community.title') }}</h1>
        <a href="{{ route('community.create') }}" class="btn-primary flex items-center gap-1.5 px-4 py-2 rounded-full text-[11px] font-bold text-white">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            {{ __('community.write') }}
        </a>
    </div>

    {{-- Segmented Control --}}
    <div class="flex bg-dark-800 p-1 rounded-2xl border border-white/[0.03]">
        @foreach(['all' => __('community.all'), 'review' => __('community.type_review'), 'tip' => __('community.type_tip'), 'realtime' => __('community.type_realtime')] as $val => $label)
        <a href="{{ route('community.index', ['type' => $val]) }}"
           class="flex-1 text-center py-2.5 rounded-xl text-[11px] font-semibold transition-all
                  {{ $type === $val ? 'gradient-accent text-white shadow-glow-sm' : 'text-gray-500' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>

    @if($posts->isEmpty())
        <x-empty-state :message="__('community.empty')"
            :action="route('community.create')" :actionLabel="__('community.empty_cta')" />
    @else
        <div class="space-y-2.5">
            @foreach($posts as $post)
                <x-post-card :post="$post" />
            @endforeach
        </div>

        <div class="mt-5">
            {{ $posts->appends(request()->query())->links('components.pagination') }}
        </div>
    @endif
</div>
@endsection
