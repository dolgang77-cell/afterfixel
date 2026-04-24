@extends('md-dashboard.layout')
@section('title', '담당 파티')
@section('content')
<div class="space-y-4">
    <div>
        <h1 class="text-[22px] font-black text-white">담당 파티</h1>
        <p class="mt-1 text-[12px] text-slate-400">행사 소개, 가이드, 이미지 순서를 모바일에서 바로 수정할 수 있습니다.</p>
    </div>

    @if($parties->isEmpty())
        <div class="rounded-[28px] border border-white/10 bg-white/5 p-8 text-center text-[13px] text-slate-400">담당 파티가 없습니다.</div>
    @else
        <div class="space-y-3">
            @foreach($parties as $party)
                <article class="rounded-[28px] border border-white/10 bg-white/5 p-4">
                    <div class="flex items-start gap-4">
                        <img src="{{ $party->thumbnail_url }}" class="h-16 w-16 rounded-[20px] object-cover">
                        <div class="min-w-0 flex-1">
                            <h2 class="truncate text-[16px] font-black text-white">{{ $party->name }}</h2>
                            <p class="mt-1 text-[12px] text-slate-400">{{ $party->club?->name }} · {{ $party->event_date?->format('Y-m-d') }}</p>
                            @if($party->short_description)
                                <p class="mt-2 text-[12px] leading-relaxed text-slate-300">{{ Str::limit($party->short_description, 70) }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-2 text-[11px]">
                        <div class="rounded-2xl bg-slate-900/70 px-3 py-3 text-slate-300">노출 이미지 {{ $party->visible_media_count }}장</div>
                        <div class="rounded-2xl bg-slate-900/70 px-3 py-3 text-slate-300">후기 {{ $party->review_count }}건</div>
                    </div>
                    <div class="mt-4 flex gap-2">
                        <a href="{{ route('md-dashboard.parties.content', $party) }}" class="flex-1 rounded-2xl bg-white px-4 py-3 text-center text-[13px] font-black text-slate-950">소개·이미지 관리</a>
                        <a href="{{ route('parties.show', $party) }}" class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-[12px] font-semibold text-slate-200">보기</a>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
@endsection
