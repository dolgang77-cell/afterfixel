@extends('md-dashboard.layout')
@section('title', 'MD 대시보드')
@section('content')
<section class="rounded-[28px] border border-white/10 bg-white/5 p-5 shadow-2xl shadow-indigo-950/40">
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="text-[12px] font-semibold uppercase tracking-[0.24em] text-indigo-300">MD Workspace</p>
            <h1 class="mt-2 text-[24px] font-black tracking-tight text-white">{{ $md->display_name }}</h1>
            <p class="mt-2 text-[13px] leading-relaxed text-slate-300">앱에서 바로 클럽 소개 수정, 파티 이미지 업로드, 문의 답변까지 처리할 수 있습니다.</p>
        </div>
        <a href="{{ route('md-dashboard.profile') }}" class="shrink-0 rounded-2xl bg-white/10 px-3 py-2 text-[12px] font-semibold text-white">프로필</a>
    </div>
</section>

<section class="mt-4 grid grid-cols-2 gap-3">
    <a href="{{ route('md-dashboard.clubs') }}" class="rounded-[24px] border border-indigo-400/20 bg-indigo-500/10 p-4">
        <p class="text-[11px] font-semibold text-indigo-200">담당 클럽</p>
        <p class="mt-2 text-[28px] font-black text-white">{{ $clubCount }}</p>
    </a>
    <a href="{{ route('md-dashboard.parties') }}" class="rounded-[24px] border border-pink-400/20 bg-pink-500/10 p-4">
        <p class="text-[11px] font-semibold text-pink-200">담당 파티</p>
        <p class="mt-2 text-[28px] font-black text-white">{{ $partyCount }}</p>
    </a>
    <a href="{{ route('md-dashboard.inquiries', ['status' => 'pending']) }}" class="rounded-[24px] border border-amber-400/20 bg-amber-500/10 p-4">
        <p class="text-[11px] font-semibold text-amber-200">미응답 문의</p>
        <p class="mt-2 text-[28px] font-black text-white">{{ $pendingInquiries }}</p>
    </a>
    <a href="{{ route('md-dashboard.media') }}" class="rounded-[24px] border border-emerald-400/20 bg-emerald-500/10 p-4">
        <p class="text-[11px] font-semibold text-emerald-200">즉시 노출 이미지</p>
        <p class="mt-2 text-[28px] font-black text-white">{{ $liveMediaCount }}</p>
    </a>
</section>

<section class="mt-4 grid grid-cols-2 gap-3">
    <a href="{{ route('md-dashboard.clubs') }}" class="rounded-[24px] border border-white/10 bg-white/5 p-4 text-left">
        <p class="text-[13px] font-bold text-white">클럽 소개 수정</p>
        <p class="mt-1 text-[11px] text-slate-400">담당 클럽 텍스트·이미지 관리</p>
    </a>
    <a href="{{ route('md-dashboard.parties') }}" class="rounded-[24px] border border-white/10 bg-white/5 p-4 text-left">
        <p class="text-[13px] font-bold text-white">파티 소개 수정</p>
        <p class="mt-1 text-[11px] text-slate-400">행사 설명과 이미지 즉시 반영</p>
    </a>
    <a href="{{ route('md-dashboard.inquiries', ['status' => 'pending']) }}" class="rounded-[24px] border border-white/10 bg-white/5 p-4 text-left">
        <p class="text-[13px] font-bold text-white">문의 우선 처리</p>
        <p class="mt-1 text-[11px] text-slate-400">미응답 우선, 예약확정까지 바로 처리</p>
    </a>
    <a href="{{ route('md-dashboard.reviews') }}" class="rounded-[24px] border border-white/10 bg-white/5 p-4 text-left">
        <p class="text-[13px] font-bold text-white">최근 후기 {{ $recentReviewCount }}건</p>
        <p class="mt-1 text-[11px] text-slate-400">클럽/파티별 반응 빠르게 확인</p>
    </a>
</section>

@if($recentReviews->isNotEmpty())
<section class="mt-4 rounded-[28px] border border-white/10 bg-white/5 p-5">
    <div class="mb-4 flex items-center justify-between">
        <h2 class="text-[16px] font-black text-white">최근 후기</h2>
        <a href="{{ route('md-dashboard.reviews') }}" class="text-[11px] font-semibold text-indigo-200">전체 보기</a>
    </div>
    <div class="space-y-3">
        @foreach($recentReviews as $r)
            <article class="rounded-2xl border border-white/5 bg-slate-900/70 p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[13px] font-bold text-white">{{ $r->user?->name ?? '익명' }}</p>
                        <p class="text-[11px] text-slate-400">{{ $r->target_type === 'club' ? '클럽' : '파티' }} #{{ $r->target_id }}</p>
                    </div>
                    <span class="text-[11px] text-slate-500">{{ $r->created_at->format('m/d H:i') }}</span>
                </div>
                <p class="mt-3 text-[13px] leading-relaxed text-slate-300">{{ Str::limit($r->content, 88) }}</p>
            </article>
        @endforeach
    </div>
</section>
@endif
@endsection
