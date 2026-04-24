@extends('md-dashboard.layout')
@section('title', '담당 클럽')
@section('content')
<div class="space-y-4">
    <div>
        <h1 class="text-[22px] font-black text-white">담당 클럽</h1>
        <p class="mt-1 text-[12px] text-slate-400">본인에게 매핑된 클럽만 수정할 수 있습니다. 소개 수정과 이미지 업로드는 즉시 반영됩니다.</p>
    </div>

    @if($clubs->isEmpty())
        <div class="rounded-[28px] border border-white/10 bg-white/5 p-8 text-center text-[13px] text-slate-400">담당 클럽이 없습니다.</div>
    @else
        <div class="space-y-3">
            @foreach($clubs as $club)
                <article class="rounded-[28px] border border-white/10 bg-white/5 p-4">
                    <div class="flex items-start gap-4">
                        <img src="{{ $club->thumbnail_url }}" class="h-16 w-16 rounded-[20px] object-cover">
                        <div class="min-w-0 flex-1">
                            <h2 class="truncate text-[16px] font-black text-white">{{ $club->name }}</h2>
                            <p class="mt-1 text-[12px] text-slate-400">{{ $club->area }} · {{ $club->genre }}</p>
                            @if($club->short_description)
                                <p class="mt-2 text-[12px] leading-relaxed text-slate-300">{{ Str::limit($club->short_description, 70) }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-2 text-[11px]">
                        <div class="rounded-2xl bg-slate-900/70 px-3 py-3 text-slate-300">노출 이미지 {{ $club->visible_media_count }}장</div>
                        <div class="rounded-2xl bg-slate-900/70 px-3 py-3 text-slate-300">후기 {{ $club->review_count }}건</div>
                    </div>
                    <div class="mt-4 flex gap-2">
                        <a href="{{ route('md-dashboard.clubs.content', $club) }}" class="flex-1 rounded-2xl bg-white px-4 py-3 text-center text-[13px] font-black text-slate-950">소개·이미지 관리</a>
                        <a href="{{ route('clubs.show', $club) }}" class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-[12px] font-semibold text-slate-200">보기</a>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
@endsection
