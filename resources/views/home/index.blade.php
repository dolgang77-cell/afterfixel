@extends('layouts.app')
@section('title', '홈')

@section('content')
@php
    $featuredAreas = \App\Services\GeoService::featuredAreas();
    $discoveryAreas = \App\Services\GeoService::discoveryAreas();
@endphp
<!-- 히어로 섹션 -->
<div class="relative overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-b from-purple-900/30 via-dark-950/50 to-dark-950 pointer-events-none"></div>
    <div class="relative px-4 pt-6 pb-8">
        <div class="mb-1">
            <p class="text-gray-400 text-sm">{{ now()->format('M월 d일 (D)') }}</p>
            <h1 class="text-2xl font-bold text-white mt-1">
                오늘 밤, 어디로 갈까요? 🎵
            </h1>
        </div>

        <!-- 메인 CTA -->
        <a href="/tour" class="mt-4 flex items-center justify-between bg-gradient-to-r from-purple-600 to-pink-600 rounded-2xl px-5 py-4 shadow-lg shadow-purple-900/40">
            <div>
                <p class="text-white font-bold text-lg">오늘 추천받기</p>
                <p class="text-purple-200 text-sm mt-0.5">AI가 최적 클럽투어 루트를 만들어드려요</p>
            </div>
            <div class="bg-white/20 rounded-xl p-3">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                </svg>
            </div>
        </a>

        <!-- 빠른 필터 칩 -->
        <div class="flex gap-2 mt-4 overflow-x-auto scrollbar-hide pb-1">
            @foreach(array_merge(['전체'], $featuredAreas) as $area)
            <a href="/clubs?area={{ $area === '전체' ? '' : $area }}"
               class="flex-shrink-0 px-4 py-1.5 rounded-full text-sm font-medium border
                      {{ $area === '전체' ? 'bg-neon-purple text-white border-transparent' : 'text-gray-300 border-white/20 bg-dark-800' }}">
                {{ $area }}
            </a>
            @endforeach
        </div>
    </div>
</div>

<!-- 오늘의 파티 -->
@if($todayParties->count())
<section class="px-4 mb-8">
    <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
            <h2 class="text-base font-bold text-white">오늘의 파티</h2>
        </div>
        <a href="/parties?date=today" class="text-xs text-gray-400 hover:text-neon-purple transition-colors">전체보기 →</a>
    </div>
    <div class="flex gap-3 overflow-x-auto scrollbar-hide pb-2">
        @foreach($todayParties as $party)
        <a href="/parties/{{ $party->slug }}" class="flex-shrink-0 w-64 bg-dark-800 rounded-2xl overflow-hidden border border-white/5 card-hover">
            <div class="relative h-36 overflow-hidden">
                @php $thumbnailSrcset = $party->thumbnail_srcset; @endphp
                <img src="{{ $party->thumbnail_url }}" alt="{{ $party->name }}"
                     class="w-full h-full object-cover" loading="lazy" decoding="async"
                     @if($thumbnailSrcset) srcset="{{ $thumbnailSrcset }}" sizes="256px" @endif>
                <div class="absolute inset-0 bg-gradient-to-t from-dark-900 via-transparent to-transparent"></div>
                <div class="absolute top-2 left-2">
                    <span class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">오늘</span>
                </div>
                <div class="absolute bottom-2 right-2">
                    <span class="bg-dark-900/80 text-white text-xs px-2 py-0.5 rounded-full">{{ $party->ticket_price_text }}</span>
                </div>
            </div>
            <div class="p-3">
                <p class="text-white font-semibold text-sm truncate">{{ $party->name }}</p>
                <p class="text-gray-400 text-xs mt-0.5">{{ $party->club->name ?? '' }} · {{ $party->club->area ?? '' }}</p>
                <p class="text-gray-500 text-xs mt-1 truncate">{{ $party->lineup }}</p>
                <div class="flex items-center gap-1 mt-2">
                    <span class="bg-dark-700 text-neon-purple text-xs px-2 py-0.5 rounded-full">{{ $party->genre }}</span>
                    @if($party->start_time)
                    <span class="text-gray-500 text-xs">{{ $party->start_time }}</span>
                    @endif
                </div>
            </div>
        </a>
        @endforeach
    </div>
</section>
@endif

<!-- 핫한 클럽 -->
<section class="px-4 mb-8">
    <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-2">
            <span class="text-orange-400">🔥</span>
            <h2 class="text-base font-bold text-white">핫한 클럽</h2>
        </div>
        <a href="/clubs" class="text-xs text-gray-400 hover:text-neon-purple transition-colors">전체보기 →</a>
    </div>
    <div class="grid grid-cols-2 gap-3">
        @foreach($hotClubs as $i => $club)
        <a href="/clubs/{{ $club->slug }}" class="relative bg-dark-800 rounded-2xl overflow-hidden border border-white/5 card-hover {{ $i === 0 ? 'col-span-2 h-44' : 'h-36' }}">
            @php $thumbnailSrcset = $club->thumbnail_srcset; @endphp
            <img src="{{ $club->thumbnail_url }}" alt="{{ $club->name }}"
                 class="w-full h-full object-cover" loading="lazy" decoding="async"
                 @if($thumbnailSrcset) srcset="{{ $thumbnailSrcset }}" sizes="{{ $i === 0 ? '(max-width: 640px) 100vw, 640px' : '(max-width: 640px) 50vw, 320px' }}" @endif>
            <div class="absolute inset-0 bg-gradient-to-t from-dark-950 via-dark-950/30 to-transparent"></div>

            @if($i === 0)
            <div class="absolute top-3 left-3">
                <span class="bg-neon-purple/90 text-white text-xs font-bold px-2 py-0.5 rounded-full">#1 인기</span>
            </div>
            @endif

            <div class="absolute bottom-0 left-0 right-0 p-3">
                <p class="text-white font-bold text-sm">{{ $club->name }}</p>
                <div class="flex items-center justify-between mt-0.5">
                    <span class="text-gray-300 text-xs">{{ $club->area }} · {{ $club->genre }}</span>
                    <div class="flex items-center gap-0.5">
                        <svg class="w-3 h-3 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <span class="text-yellow-400 text-xs font-medium">{{ number_format($club->rating_avg, 1) }}</span>
                    </div>
                </div>
            </div>
        </a>
        @endforeach
    </div>
</section>

<!-- 지역별 빠른 탐색 -->
<section class="px-4 mb-8">
    <h2 class="text-base font-bold text-white mb-3">지역별 탐색</h2>
    <div class="grid grid-cols-4 gap-2">
        @foreach($discoveryAreas as $a)
        <a href="/clubs?area={{ $a['label'] }}"
           class="bg-gradient-to-br {{ $a['color'] }} rounded-2xl p-3 flex flex-col items-center justify-center gap-1 border border-white/10 aspect-square card-hover">
            <span class="text-2xl">{{ $a['emoji'] }}</span>
            <span class="text-white text-xs font-medium">{{ $a['label'] }}</span>
        </a>
        @endforeach
    </div>
</section>

<!-- 실시간 후기 -->
@if($recentPosts->count())
<section class="px-4 mb-6">
    <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
            <h2 class="text-base font-bold text-white">실시간 제보 & 후기</h2>
        </div>
        <a href="/community" class="text-xs text-gray-400 hover:text-neon-purple transition-colors">전체보기 →</a>
    </div>
    <div class="space-y-2">
        @foreach($recentPosts as $post)
        <a href="/community/{{ $post->id }}" class="block bg-dark-800 rounded-xl px-4 py-3 border border-white/5 hover:border-purple-500/30 transition-colors">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center flex-shrink-0 text-sm font-bold text-white">
                    {{ mb_substr($post->nickname, 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="text-white text-xs font-medium">{{ $post->nickname }}</span>
                        <span class="text-xs px-1.5 py-0.5 rounded-full
                            {{ $post->type === 'realtime' ? 'bg-red-900/50 text-red-400' : ($post->type === 'tip' ? 'bg-blue-900/50 text-blue-400' : 'bg-purple-900/50 text-purple-400') }}">
                            {{ $post->type_text }}
                        </span>
                    </div>
                    <p class="text-gray-300 text-sm mt-0.5 truncate">{{ $post->title ?? $post->content }}</p>
                    @if($post->club)
                    <p class="text-gray-500 text-xs mt-0.5">{{ $post->club->name }}</p>
                    @endif
                </div>
                <span class="text-gray-600 text-xs flex-shrink-0">{{ $post->created_at->diffForHumans() }}</span>
            </div>
        </a>
        @endforeach
    </div>
</section>
@endif
@endsection
