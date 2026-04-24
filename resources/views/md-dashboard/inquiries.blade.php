@extends('md-dashboard.layout')
@section('title', '문의 관리')
@section('content')
<div class="space-y-4">
    <div>
        <h1 class="text-[22px] font-black text-white">문의 관리</h1>
        <p class="mt-1 text-[12px] text-slate-400">미응답 문의를 우선 처리하고, 답변 후 상태를 예약확정 또는 상담완료로 바로 전환할 수 있습니다.</p>
    </div>

    <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
        <a href="{{ route('md-dashboard.inquiries', ['queue' => 'unanswered']) }}" class="rounded-[24px] border border-rose-400/15 bg-rose-500/5 p-4">
            <p class="text-[11px] text-rose-200/80">미응답 문의</p>
            <p class="mt-2 text-[22px] font-black text-white">{{ $leadSummary['pendingCount'] }}</p>
            <p class="mt-1 text-[11px] text-slate-400">바로 답변해야 할 건수</p>
        </a>
        <a href="{{ route('md-dashboard.inquiries', ['queue' => 'delayed']) }}" class="rounded-[24px] border border-amber-400/15 bg-amber-500/5 p-4">
            <p class="text-[11px] text-amber-200/80">응답 지연</p>
            <p class="mt-2 text-[22px] font-black text-white">{{ $leadSummary['delayedCount'] }}</p>
            <p class="mt-1 text-[11px] text-slate-400">30분 이상 첫 답변 대기</p>
        </a>
        <a href="{{ route('md-dashboard.inquiries', ['queue' => 'follow_up']) }}" class="rounded-[24px] border border-sky-400/15 bg-sky-500/5 p-4">
            <p class="text-[11px] text-sky-200/80">상담 진행</p>
            <p class="mt-2 text-[22px] font-black text-white">{{ $leadSummary['followUpCount'] }}</p>
            <p class="mt-1 text-[11px] text-slate-400">추가 안내가 필요한 문의</p>
        </a>
        <a href="{{ route('md-dashboard.inquiries', ['queue' => 'confirmation_waiting']) }}" class="rounded-[24px] border border-violet-400/15 bg-violet-500/5 p-4">
            <p class="text-[11px] text-violet-200/80">확정 대기</p>
            <p class="mt-2 text-[22px] font-black text-white">{{ $leadSummary['confirmationWaitingCount'] }}</p>
            <p class="mt-1 text-[11px] text-slate-400">답변 후 후속 확정이 필요한 문의</p>
        </a>
    </div>

    <div class="grid grid-cols-3 gap-3">
        <a href="{{ route('md-dashboard.inquiries', ['queue' => 'sla_10']) }}" class="rounded-[24px] border border-sky-400/15 bg-sky-500/5 p-4">
            <p class="text-[11px] text-sky-200/80">SLA 10분</p>
            <p class="mt-2 text-[22px] font-black text-white">{{ $leadSummary['sla10Count'] }}</p>
            <p class="mt-1 text-[11px] text-slate-400">10분 이상 첫 답변 대기</p>
        </a>
        <a href="{{ route('md-dashboard.inquiries', ['queue' => 'sla_30']) }}" class="rounded-[24px] border border-amber-400/15 bg-amber-500/5 p-4">
            <p class="text-[11px] text-amber-200/80">SLA 30분</p>
            <p class="mt-2 text-[22px] font-black text-white">{{ $leadSummary['sla30Count'] }}</p>
            <p class="mt-1 text-[11px] text-slate-400">30분 이상 첫 답변 대기</p>
        </a>
        <a href="{{ route('md-dashboard.inquiries', ['queue' => 'sla_60']) }}" class="rounded-[24px] border border-rose-400/15 bg-rose-500/5 p-4">
            <p class="text-[11px] text-rose-200/80">SLA 60분</p>
            <p class="mt-2 text-[22px] font-black text-white">{{ $leadSummary['sla60Count'] }}</p>
            <p class="mt-1 text-[11px] text-slate-400">60분 이상 첫 답변 대기</p>
        </a>
    </div>

    <form method="GET" class="flex gap-2 overflow-x-auto scrollbar-hide md-scroll-snap overscroll-x-contain touch-pan-x">
        @if(request()->filled('queue'))
            <input type="hidden" name="queue" value="{{ request('queue') }}">
        @endif
        @if(request()->filled('intent_type'))
            <input type="hidden" name="intent_type" value="{{ request('intent_type') }}">
        @endif
        @foreach(['' => '전체', 'pending' => '미응답', 'in_progress' => '상담중', 'answered' => '답변완료', 'reservation_confirmed' => '예약확정', 'consultation_completed' => '상담완료'] as $value => $label)
            <button name="status" value="{{ $value }}" class="shrink-0 rounded-full px-4 py-2 text-[12px] font-semibold {{ request('status', '') === $value ? 'bg-indigo-500 text-white' : 'bg-white/5 text-slate-300' }}">{{ $label }}</button>
        @endforeach
    </form>

    <form method="GET" class="flex gap-2 overflow-x-auto scrollbar-hide md-scroll-snap overscroll-x-contain touch-pan-x">
        @if(request()->filled('queue'))
            <input type="hidden" name="queue" value="{{ request('queue') }}">
        @endif
        @if(request()->filled('status'))
            <input type="hidden" name="status" value="{{ request('status') }}">
        @endif
        @foreach(['' => '전체 유형'] + \App\Models\Inquiry::$intentLabels as $value => $label)
            <button name="intent_type" value="{{ $value }}" class="shrink-0 rounded-full px-4 py-2 text-[12px] font-semibold {{ request('intent_type', '') === $value ? 'bg-amber-500 text-slate-950' : 'bg-white/5 text-slate-300' }}">{{ $label }}</button>
        @endforeach
    </form>

    @if(request()->filled('queue') || request()->filled('status') || request()->filled('intent_type'))
        <div class="flex justify-end">
            <a href="{{ route('md-dashboard.inquiries') }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-[12px] font-semibold text-slate-300">필터 초기화</a>
        </div>
    @endif

    <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
        <div class="rounded-[24px] border border-sky-400/15 bg-sky-500/5 p-4">
            <p class="text-[11px] text-sky-200/80">평균 첫 답변</p>
            <p class="mt-2 text-[18px] font-black text-white">{{ $leadSummary['avgFirstResponseText'] }}</p>
            <p class="mt-1 text-[11px] text-slate-400">최근 30일 공개 답변 기준</p>
        </div>
        <div class="rounded-[24px] border border-violet-400/15 bg-violet-500/5 p-4">
            <p class="text-[11px] text-violet-200/80">예약 확정률</p>
            <p class="mt-2 text-[22px] font-black text-white">{{ $leadSummary['confirmationRate'] !== null ? $leadSummary['confirmationRate'].'%' : '준비중' }}</p>
            <p class="mt-1 text-[11px] text-slate-400">최근 30일 문의 기준</p>
        </div>
        <div class="rounded-[24px] border border-amber-400/15 bg-amber-500/5 p-4">
            <p class="text-[11px] text-amber-200/80">평균 예상 객단가</p>
            <p class="mt-2 text-[18px] font-black text-white">{{ $leadSummary['avgEstimatedValueText'] }}</p>
            <p class="mt-1 text-[11px] text-slate-400">예산 입력/인원 추정 기준</p>
        </div>
    </div>

    @if($leadSummary['priorityItems']->isNotEmpty())
    <section class="space-y-2">
        <div class="flex items-center justify-between px-1">
            <div>
                <p class="text-[14px] font-black text-white">우선 확인 문의</p>
                <p class="mt-1 text-[11px] text-slate-500">지연 여부와 우선순위를 기준으로 상위 문의를 먼저 보여줍니다.</p>
            </div>
        </div>
        <div class="space-y-2">
            @foreach($leadSummary['priorityItems'] as $priorityInquiry)
                <a href="{{ route('md-dashboard.inquiries.show', $priorityInquiry) }}" class="flex items-center justify-between gap-3 rounded-[24px] border border-white/10 bg-white/5 p-4">
                    <div class="min-w-0">
                        <p class="truncate text-[13px] font-bold text-white">{{ $priorityInquiry->subject }}</p>
                        <p class="mt-1 text-[11px] text-slate-400">{{ $priorityInquiry->user?->name ?? '-' }} · {{ $priorityInquiry->latestConversationAuthorLabel() }} · {{ $priorityInquiry->lastPublicReplyText() }}</p>
                    </div>
                    <div class="shrink-0 text-right">
                        <span class="rounded-full px-2 py-1 text-[10px] font-semibold {{ $priorityInquiry->statusToneClass() }}">{{ $priorityInquiry->statusLabel }}</span>
                        @if($priorityInquiry->slaLabel())
                            <p class="mt-1">
                                <span class="rounded-full px-2 py-1 text-[10px] font-semibold {{ $priorityInquiry->slaToneClass() }}">{{ $priorityInquiry->slaLabel() }}</span>
                            </p>
                        @endif
                        <p class="mt-1 text-[10px] font-semibold {{ $priorityInquiry->leadGradeTone() }}">{{ $priorityInquiry->leadGradeLabel() }} · {{ $priorityInquiry->leadPriorityScore() }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
    @endif

    <div class="space-y-3">
        @forelse($inquiries as $inq)
            @php
                $priority = $inq->leadPriorityScore();
                $latestPreview = $inq->latestConversationPreview();
                $slaLabel = $inq->slaLabel();
            @endphp
            <article class="rounded-[28px] border {{ $inq->slaLevel() === 'critical' ? 'border-rose-400/30' : ($inq->slaLevel() === 'warning' ? 'border-amber-400/25' : ($inq->slaLevel() === 'attention' ? 'border-sky-400/20' : ($inq->status === 'pending' ? 'border-rose-400/20' : 'border-white/10'))) }} bg-white/5 p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="truncate text-[15px] font-black text-white">{{ $inq->subject }}</h2>
                        <p class="mt-1 text-[12px] text-slate-400">{{ $inq->user?->name ?? '-' }} · {{ $inq->target_type === 'club' ? '클럽' : '파티' }} #{{ $inq->target_id }} · {{ $inq->intent_label }}</p>
                    </div>
                    <div class="flex flex-col items-end gap-1">
                        <span class="rounded-full px-2 py-1 text-[10px] font-semibold {{ $inq->statusToneClass() }}">{{ $inq->statusLabel }}</span>
                        @if($slaLabel)
                            <span class="rounded-full px-2 py-1 text-[10px] font-semibold {{ $inq->slaToneClass() }}">{{ $slaLabel }}</span>
                        @endif
                        <span class="text-[10px] font-semibold {{ $inq->leadGradeTone() }}">{{ $inq->leadGradeLabel() }} · {{ $priority }}</span>
                    </div>
                </div>

                <div class="mt-3 rounded-[22px] border border-white/10 bg-slate-900/60 px-3 py-3">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-[11px] font-semibold text-slate-200">최근 대화</p>
                        <span class="text-[10px] text-slate-500">{{ $inq->latestConversationAuthorLabel() }} · {{ $inq->lastPublicReplyText() }}</span>
                    </div>
                    <p class="mt-2 text-[12px] leading-relaxed text-slate-300">{{ $latestPreview }}</p>
                </div>

                <div class="mt-3 flex flex-wrap gap-2 text-[11px] text-slate-400">
                    @if($inq->visit_date)
                        <span class="rounded-full bg-white/5 px-2 py-1">방문 {{ $inq->visit_date->format('m/d') }}</span>
                    @endif
                    @if($inq->party_size)
                        <span class="rounded-full bg-white/5 px-2 py-1">{{ $inq->party_size }}명</span>
                    @endif
                    @if($inq->budget_text)
                        <span class="rounded-full bg-white/5 px-2 py-1">예산 {{ $inq->budget_text }}</span>
                    @endif
                    @if($inq->visit_time_slot_label)
                        <span class="rounded-full bg-white/5 px-2 py-1">{{ $inq->visit_time_slot_label }}</span>
                    @endif
                    @if($inq->estimatedValueText())
                        <span class="rounded-full bg-white/5 px-2 py-1">{{ $inq->estimatedValueText() }}</span>
                    @endif
                    @if($slaLabel)
                        <span class="rounded-full px-2 py-1 font-semibold {{ $inq->slaToneClass() }}">{{ $slaLabel }}</span>
                    @endif
                    <span class="rounded-full bg-white/5 px-2 py-1">첫 답변 {{ $inq->firstResponseText() }}</span>
                    <span class="rounded-full bg-white/5 px-2 py-1">최근 답변 {{ $inq->lastPublicReplyText() }}</span>
                </div>
                <div class="mt-4 flex items-center justify-between gap-3">
                    <span class="text-[11px] text-slate-500">{{ $inq->created_at->format('m-d H:i') }}</span>
                    <div class="flex items-center gap-2">
                        @if($inq->status === 'pending' && !$slaLabel)
                            <span class="rounded-full bg-rose-500/10 px-3 py-2 text-[11px] font-semibold text-rose-200">빠른 첫 답변 필요</span>
                        @endif
                        <a href="{{ route('md-dashboard.inquiries.show', $inq) }}#reply-form" class="rounded-full bg-white px-4 py-2 text-[12px] font-black text-slate-950">상세·답변</a>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-[28px] border border-white/10 bg-white/5 p-8 text-center text-[13px] text-slate-400">문의가 없습니다.</div>
        @endforelse
    </div>

    <div>{{ $inquiries->links() }}</div>
</div>
@endsection
