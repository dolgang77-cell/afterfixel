@extends('md-dashboard.layout')
@section('title', '미디어 관리')
@section('content')
<div class="space-y-4">
    <div>
        <h1 class="text-[22px] font-black text-white">미디어 관리</h1>
        <p class="mt-1 text-[12px] text-slate-400">MD 권한으로 업로드한 프로필·클럽·파티 이미지는 자동 승인됩니다. 일반 사용자 경로 이미지는 기존 승인 정책을 유지합니다.</p>
    </div>

    <form method="GET" class="flex gap-2 overflow-x-auto scrollbar-hide md-scroll-snap overscroll-x-contain touch-pan-x">
        @foreach(['' => '전체', 'md_profile' => '프로필', 'club' => '클럽', 'party' => '파티'] as $value => $label)
            <button name="owner_type" value="{{ $value }}" class="shrink-0 rounded-full px-4 py-2 text-[12px] font-semibold {{ request('owner_type', '') === $value ? 'bg-indigo-500 text-white' : 'bg-white/5 text-slate-300' }}">{{ $label }}</button>
        @endforeach
    </form>

    <div class="grid grid-cols-2 gap-3">
        @forelse($media as $m)
            <div class="overflow-hidden rounded-[24px] border border-white/10 bg-white/5">
                <img src="{{ $m->file_url }}" class="h-32 w-full object-cover">
                <div class="space-y-2 p-3">
                    <div class="flex items-center justify-between">
                        <span class="rounded-full {{ $m->approval_status === 'approved' ? 'bg-emerald-500/15 text-emerald-200' : 'bg-amber-500/15 text-amber-200' }} px-2 py-1 text-[10px] font-semibold">{{ $m->approval_status === 'approved' ? '승인·노출중' : $m->approval_status }}</span>
                        <span class="text-[10px] text-slate-500">{{ $m->created_at->format('m/d') }}</span>
                    </div>
                    <p class="text-[11px] text-slate-400">{{ $m->owner_type }} · 순서 {{ $m->sort_order + 1 }}</p>
                    <div class="grid grid-cols-3 gap-2">
                        <form method="POST" action="{{ route('md-dashboard.media.order', $m) }}">@csrf @method('PATCH')
                            <input type="hidden" name="direction" value="up">
                            <button class="w-full rounded-xl bg-white/5 px-2 py-2 text-[11px] font-semibold text-slate-200">위로</button>
                        </form>
                        <form method="POST" action="{{ route('md-dashboard.media.order', $m) }}">@csrf @method('PATCH')
                            <input type="hidden" name="direction" value="down">
                            <button class="w-full rounded-xl bg-white/5 px-2 py-2 text-[11px] font-semibold text-slate-200">아래</button>
                        </form>
                        <form method="POST" action="{{ route('md-dashboard.media.destroy', $m) }}">@csrf @method('DELETE')
                            <button class="w-full rounded-xl bg-rose-500/15 px-2 py-2 text-[11px] font-semibold text-rose-200">삭제</button>
                        </form>
                    </div>
                    @if($m->rejected_reason)
                        <p class="text-[11px] text-rose-300">사유: {{ $m->rejected_reason }}</p>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-2 rounded-[28px] border border-white/10 bg-white/5 p-8 text-center text-[13px] text-slate-400">표시할 이미지가 없습니다.</div>
        @endforelse
    </div>
    <div>{{ $media->links() }}</div>
</div>
@endsection
