@extends('layouts.app')
@section('title', __('fav.title'))

@section('content')
<div class="px-4 py-6">
    <div class="flex items-center gap-3 mb-5">
        <a href="{{ route('my.index') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-dark-700/60 text-gray-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        </a>
        <h1 class="text-[20px] font-extrabold text-white">{{ __('fav.title') }}</h1>
    </div>

    {{-- 탭 --}}
    <div class="flex gap-2 mb-5">
        @foreach(['party' => __('nav.party').' '.$counts['party'], 'club' => __('nav.club').' '.$counts['club'], 'tour' => __('tour.tour_label').' '.$counts['tour']] as $key => $label)
            <a href="?tab={{ $key }}"
               class="px-3.5 py-1.5 rounded-full text-[12px] font-medium transition-all {{ $tab === $key ? 'gradient-accent text-white shadow-glow-sm' : 'bg-dark-700/60 text-gray-400 border border-white/[0.06]' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="space-y-2.5">
        @if($tab === 'party')
            @forelse($parties as $party)
                <div class="card p-4 flex items-center gap-3">
                    <a href="{{ route('parties.show', $party) }}" class="flex items-center gap-3 flex-1 min-w-0">
                        <div class="w-14 h-14 rounded-xl overflow-hidden shrink-0 bg-dark-700">
                            <img src="{{ $party->thumbnail_url }}" class="w-full h-full object-cover" alt="">
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[14px] font-bold text-white truncate">{{ $party->name }}</p>
                            <p class="text-[12px] text-gray-400 mt-0.5">{{ $party->club?->name }} · {{ trans_auto($party->club?->area ?? '') }}</p>
                            <p class="text-[11px] text-gray-500 mt-0.5">{{ $party->event_date?->format('n/j (D)') }} {{ $party->start_time }}</p>
                        </div>
                    </a>
                    <form action="{{ route('favorites.toggle', ['type' => 'party', 'id' => $party->id]) }}" method="POST" class="shrink-0">
                        @csrf
                        <button type="submit" class="w-8 h-8 flex items-center justify-center text-pink-500 active:scale-90 transition-transform">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/></svg>
                        </button>
                    </form>
                </div>
            @empty
                <x-empty-state message="{{ __('fav.no_party') }}" :action="route('parties.index')" actionLabel="{{ __('fav.browse_party') }}" />
            @endforelse
        @elseif($tab === 'club')
            @forelse($clubs as $club)
                <div class="card p-4 flex items-center gap-3">
                    <a href="{{ route('clubs.show', $club) }}" class="flex items-center gap-3 flex-1 min-w-0">
                        <div class="w-14 h-14 rounded-xl overflow-hidden shrink-0 bg-dark-700">
                            <img src="{{ $club->thumbnail_url }}" class="w-full h-full object-cover" alt="">
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[14px] font-bold text-white truncate">{{ $club->name }}</p>
                            <p class="text-[12px] text-gray-400 mt-0.5">{{ trans_auto($club->area) }} · {{ trans_auto($club->genre) }}</p>
                        </div>
                    </a>
                    <form action="{{ route('favorites.toggle', ['type' => 'club', 'id' => $club->id]) }}" method="POST" class="shrink-0">
                        @csrf
                        <button type="submit" class="w-8 h-8 flex items-center justify-center text-pink-500 active:scale-90 transition-transform">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/></svg>
                        </button>
                    </form>
                </div>
            @empty
                <x-empty-state message="{{ __('fav.no_club') }}" :action="route('clubs.index')" actionLabel="{{ __('fav.browse_club') }}" />
            @endforelse
        @else
            @forelse($tours as $tour)
                <div class="card p-4">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0">
                            <p class="text-[13px] font-semibold text-gray-200">{{ $tour->input_condition['area'] ?? '' }} {{ __('tour.tour_label') }}</p>
                            <p class="text-[11px] text-gray-500">{{ $tour->created_at->format('n/j') }} · {{ number_format($tour->total_cost ?? 0) }}원</p>
                        </div>
                        <form action="{{ route('favorites.toggle', ['type' => 'tour', 'id' => $tour->id]) }}" method="POST">
                            @csrf
                            <button type="submit" class="text-pink-500 active:scale-90 transition-transform">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <x-empty-state message="{{ __('fav.no_tour') }}" :action="route('tour.index')" actionLabel="{{ __('fav.browse_tour') }}" />
            @endforelse
        @endif
    </div>
</div>
@endsection
