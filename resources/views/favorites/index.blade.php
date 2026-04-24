@extends('layouts.app')
@section('title', '찜한 파티')

@section('content')
<div class="px-4 py-6">
    <div class="flex items-center gap-3 mb-5">
        <a href="{{ url()->previous() }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-dark-700/60 text-gray-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        </a>
        <h1 class="text-[20px] font-extrabold text-white">찜한 파티</h1>
        <span class="text-[12px] text-gray-500">{{ $favorites->count() }}개</span>
    </div>

    <div class="space-y-2.5">
        @forelse($favorites as $party)
            <a href="{{ route('parties.show', $party) }}" class="card p-4 block">
                <div class="flex gap-3">
                    <div class="w-16 h-16 rounded-xl overflow-hidden shrink-0 bg-dark-700">
                        <img src="{{ $party->thumbnail_url }}" alt="{{ $party->name }}" class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[14px] font-bold text-white truncate">{{ $party->name }}</p>
                        <p class="text-[12px] text-gray-400 mt-0.5">{{ $party->club?->name }} · {{ $party->club?->area }}</p>
                        <div class="flex items-center gap-2 mt-1.5">
                            <span class="text-[11px] text-gray-500">{{ $party->event_date?->format('n/j (D)') }}</span>
                            <span class="text-[11px] text-gray-600">{{ $party->start_time }}</span>
                            @if($party->is_upcoming)
                                <span class="text-[10px] font-bold text-green-400 bg-green-400/10 px-1.5 py-0.5 rounded">D-{{ now()->diffInDays($party->event_date) }}</span>
                            @endif
                        </div>
                    </div>
                    <form action="{{ route('favorites.toggle', $party) }}" method="POST" class="shrink-0">
                        @csrf
                        <button type="submit" class="w-8 h-8 flex items-center justify-center text-pink-500">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/></svg>
                        </button>
                    </form>
                </div>
            </a>
        @empty
            <x-empty-state message="찜한 파티가 없습니다" :action="route('parties.index')" actionLabel="파티 둘러보기" />
        @endforelse
    </div>
</div>
@endsection
