@extends('layouts.app')
@section('title', $md->display_name . ' - MD')

@section('content')
<div class="px-4 py-6 space-y-5">

    {{-- 프로필 헤더 --}}
    <div class="card p-5 text-center">
        @if($md->profile_image)
            @php $profileSrcset = $md->profile_image_srcset; @endphp
            <img src="{{ $md->profile_image }}" class="w-20 h-20 rounded-full object-cover mx-auto mb-3 border-2 border-accent/30" decoding="async"
                 @if($profileSrcset) srcset="{{ $profileSrcset }}" sizes="80px" @endif>
        @else
            <div class="w-20 h-20 rounded-full gradient-accent flex items-center justify-center mx-auto mb-3 text-2xl font-bold text-white">{{ mb_substr($md->display_name, 0, 1) }}</div>
        @endif
        <h1 class="text-[18px] font-extrabold text-white">{{ $md->display_name }}</h1>
        @if($md->affiliation)
            <p class="text-[12px] text-gray-500 mt-0.5">{{ $md->affiliation }}</p>
        @endif

        {{-- 지역/장르 태그 --}}
        <div class="flex flex-wrap justify-center gap-1.5 mt-3">
            @foreach($md->areas ?? [] as $area)
                <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">{{ trans_auto($area) }}</span>
            @endforeach
            @foreach($md->genres ?? [] as $genre)
                <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-pink-500/10 text-pink-400 border border-pink-500/20">{{ trans_auto($genre) }}</span>
            @endforeach
        </div>

        {{-- 외부 링크 --}}
        @if($md->external_link)
        <a href="{{ $md->external_link }}" target="_blank" rel="noopener"
           class="inline-flex items-center gap-2 mt-4 px-5 py-2.5 rounded-2xl bg-dark-700 border border-white/[0.06] text-[12px] font-semibold text-gray-300 active:scale-[0.98] transition-transform">
            <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m9.586-5.828a4.5 4.5 0 00-6.364 0L4.5 11.25a4.5 4.5 0 000 6.364l.257.257"/></svg>
            {{ __('md.contact') }}
        </a>
        @endif
    </div>

    {{-- 소개 --}}
    @if($md->intro)
    <div class="card p-4">
        <h2 class="text-[13px] font-bold text-gray-300 mb-2">{{ __('md.intro') }}</h2>
        <p class="text-[13px] text-gray-400 leading-relaxed whitespace-pre-wrap">{{ trans_auto($md->intro) }}</p>
    </div>
    @endif

    {{-- 담당 클럽 --}}
    @if($md->clubs->isNotEmpty())
    <div>
        <h2 class="text-[15px] font-bold text-white mb-3 px-1">{{ __('md.clubs') }}</h2>
        <div class="space-y-2">
            @foreach($md->clubs as $club)
            <a href="{{ route('clubs.show', $club) }}" class="card flex items-center gap-3.5 p-3.5">
                <div class="w-12 h-12 rounded-xl overflow-hidden shrink-0 bg-dark-700">
                    <img src="{{ $club->thumbnail_url }}" class="w-full h-full object-cover" alt="">
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[13px] font-semibold text-gray-200 truncate">{{ $club->name }}</p>
                    <p class="text-[11px] text-gray-500">{{ trans_auto($club->area) }} · {{ trans_auto($club->genre) }}</p>
                </div>
                <svg class="w-4 h-4 text-gray-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- 담당 파티 --}}
    @if($md->parties->isNotEmpty())
    <div>
        <h2 class="text-[15px] font-bold text-white mb-3 px-1">{{ __('md.parties') }}</h2>
        <div class="space-y-2">
            @foreach($md->parties as $party)
            <a href="{{ route('parties.show', $party) }}" class="card flex items-center gap-3.5 p-3.5">
                <div class="w-12 h-12 rounded-xl overflow-hidden shrink-0 bg-dark-700">
                    <img src="{{ $party->thumbnail_url }}" class="w-full h-full object-cover" alt="">
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[13px] font-semibold text-gray-200 truncate">{{ $party->name }}</p>
                    <p class="text-[11px] text-gray-500">{{ $party->club?->name }} · {{ $party->event_date?->format('n/j') }}</p>
                </div>
                <svg class="w-4 h-4 text-gray-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            </a>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
