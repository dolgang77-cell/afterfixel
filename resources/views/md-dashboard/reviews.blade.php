@extends('md-dashboard.layout')
@section('title', '후기 조회')
@section('content')
<div class="space-y-4">
    <div>
        <h1 class="text-[22px] font-black text-white">담당 후기</h1>
        <p class="mt-1 text-[12px] text-slate-400">클럽별·파티별 후기 반응을 모바일에서 빠르게 확인합니다.</p>
    </div>

    <form method="GET" class="grid grid-cols-2 gap-2">
        <select name="target_type" class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-[12px] text-white">
            <option value="">전체 대상</option>
            <option value="club" {{ request('target_type') === 'club' ? 'selected' : '' }}>클럽</option>
            <option value="party" {{ request('target_type') === 'party' ? 'selected' : '' }}>파티</option>
        </select>
        <input type="text" name="target_id" value="{{ request('target_id') }}" placeholder="대상 ID" class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-[12px] text-white">
        <button type="submit" class="col-span-2 rounded-2xl bg-white px-4 py-3 text-[13px] font-black text-slate-950">필터 적용</button>
    </form>

    <div class="space-y-3">
        @forelse($reviews as $r)
            <article class="rounded-[28px] border border-white/10 bg-white/5 p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[14px] font-black text-white">{{ $r->user?->name ?? '익명' }}</p>
                        <p class="text-[11px] text-slate-400">{{ $r->target_type === 'club' ? '클럽' : '파티' }} #{{ $r->target_id }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[13px] font-black text-amber-200">{{ $r->rating ? $r->rating . '/5' : '-' }}</p>
                        <p class="text-[10px] text-slate-500">{{ $r->created_at->format('m-d H:i') }}</p>
                    </div>
                </div>
                <p class="mt-3 text-[13px] leading-relaxed text-slate-300">{{ $r->content }}</p>
            </article>
        @empty
            <div class="rounded-[28px] border border-white/10 bg-white/5 p-8 text-center text-[13px] text-slate-400">후기가 없습니다.</div>
        @endforelse
    </div>

    <div>{{ $reviews->links() }}</div>
</div>
@endsection
