@extends('md-dashboard.layout')
@section('title', '문의 상세')
@section('content')
<div class="space-y-4">
    <div class="flex items-center gap-3">
        <a href="{{ route('md-dashboard.inquiries') }}" class="flex h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-white/5 text-slate-300">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        </a>
        <div>
            <h1 class="text-[20px] font-black text-white">문의 상세</h1>
            <p class="text-[12px] text-slate-400">답변과 상태 변경을 한 화면에서 처리합니다.</p>
        </div>
    </div>

    <section class="rounded-[28px] border border-white/10 bg-white/5 p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h2 class="text-[18px] font-black text-white">{{ $inquiry->subject }}</h2>
                <p class="mt-1 text-[12px] text-slate-400">{{ $inquiry->user?->name ?? '-' }} · {{ $inquiry->target_type === 'club' ? '클럽' : '파티' }} #{{ $inquiry->target_id }} · {{ $inquiry->intent_label }}</p>
            </div>
            <div class="flex flex-col items-end gap-1">
                <span class="rounded-full px-2 py-1 text-[10px] font-semibold {{ $inquiry->statusToneClass() }}">{{ $inquiry->statusLabel }}</span>
                @if($inquiry->isResponseDelayed())
                    <span class="rounded-full bg-amber-500/15 px-2 py-1 text-[10px] font-semibold text-amber-200">{{ $inquiry->responseDelayText() }}</span>
                @endif
            </div>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-2 text-[12px] text-slate-300">
            <div class="rounded-2xl bg-slate-900/70 px-3 py-3">최근 대화<br><span class="text-slate-500">{{ $inquiry->latestConversationAuthorLabel() }} · {{ $inquiry->lastPublicReplyText() }}</span></div>
            <div class="rounded-2xl bg-slate-900/70 px-3 py-3">응답 상태<br><span class="{{ $inquiry->isResponseDelayed() ? 'text-amber-300' : 'text-slate-500' }}">{{ $inquiry->isResponseDelayed() ? $inquiry->responseDelayText() : '정상 응답 흐름' }}</span></div>
        </div>

        <form action="{{ route('md-dashboard.inquiries.status', $inquiry) }}" method="POST" class="mt-4 flex gap-2">
            @csrf @method('PATCH')
            <select name="status" class="flex-1 rounded-2xl border border-white/10 bg-slate-900/70 px-4 py-3 text-[13px] text-white">
                @foreach(['in_progress'=>'상담중','answered'=>'답변완료','reservation_confirmed'=>'예약확정','consultation_completed'=>'상담완료'] as $v=>$l)
                    <option value="{{ $v }}" {{ $inquiry->status===$v?'selected':'' }}>{{ $l }}</option>
                @endforeach
            </select>
            <button class="rounded-2xl bg-white px-4 py-3 text-[12px] font-black text-slate-950">변경</button>
        </form>

        <div class="mt-4 grid grid-cols-2 gap-2 text-[12px] text-slate-300">
            <div class="rounded-2xl bg-slate-900/70 px-3 py-3">방문 예정일<br><span class="text-slate-500">{{ $inquiry->visit_date?->format('Y-m-d') ?? '-' }}</span></div>
            <div class="rounded-2xl bg-slate-900/70 px-3 py-3">인원<br><span class="text-slate-500">{{ $inquiry->party_size ? $inquiry->party_size . '명' : '-' }}</span></div>
            <div class="rounded-2xl bg-slate-900/70 px-3 py-3">예산<br><span class="text-slate-500">{{ $inquiry->budget_text ?? '-' }}</span></div>
            <div class="rounded-2xl bg-slate-900/70 px-3 py-3">도착 시간대<br><span class="text-slate-500">{{ $inquiry->visit_time_slot_label ?? '-' }}</span></div>
            <div class="rounded-2xl bg-slate-900/70 px-3 py-3">구성<br><span class="text-slate-500">{{ $inquiry->gender_mix ?? '-' }}</span></div>
            <div class="rounded-2xl bg-slate-900/70 px-3 py-3">우선순위<br><span class="text-slate-500">{{ $inquiry->priorityScore() }}</span></div>
            <div class="rounded-2xl bg-slate-900/70 px-3 py-3">리드 등급<br><span class="{{ $inquiry->leadGradeTone() }}">{{ $inquiry->leadGradeLabel() }}</span></div>
            <div class="rounded-2xl bg-slate-900/70 px-3 py-3">예상 객단가<br><span class="text-slate-500">{{ $inquiry->estimatedValueText() ?? '-' }}</span></div>
            <div class="rounded-2xl bg-slate-900/70 px-3 py-3">첫 답변 시간<br><span class="text-slate-500">{{ $inquiry->firstResponseText() }}</span></div>
            <div class="rounded-2xl bg-slate-900/70 px-3 py-3">최근 답변<br><span class="text-slate-500">{{ $inquiry->lastPublicReplyText() }}</span></div>
            <div class="rounded-2xl bg-slate-900/70 px-3 py-3">대화 수<br><span class="text-slate-500">{{ $inquiry->publicReplies->count() }}회</span></div>
            <div class="rounded-2xl bg-slate-900/70 px-3 py-3">재문의 여부<br><span class="text-slate-500">{{ ($sameUserInquiryCount ?? 1) > 1 ? '재문의 고객' : '첫 문의' }}</span></div>
        </div>

        <div class="mt-4 rounded-[24px] bg-slate-900/70 p-4 text-[13px] leading-relaxed text-slate-300 whitespace-pre-wrap">{{ $inquiry->message }}</div>
        @if($inquiry->special_request)
            <div class="mt-3 rounded-[24px] bg-slate-900/70 p-4 text-[13px] leading-relaxed text-slate-300 whitespace-pre-wrap">
                <span class="mb-2 block text-[11px] font-semibold text-slate-400">추가 요청</span>
                {{ $inquiry->special_request }}
            </div>
        @endif
    </section>

    @if($inquiry->publicReplies->isNotEmpty())
    <section class="space-y-3">
        @foreach($inquiry->publicReplies as $reply)
            <article class="rounded-[24px] border {{ $reply->author_type === 'md' ? 'border-indigo-400/20 bg-indigo-500/5' : 'border-white/10 bg-white/5' }} p-4">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold {{ $reply->author_type === 'md' ? 'text-indigo-200' : 'text-slate-300' }}">{{ $reply->author_type === 'md' ? 'MD 답변' : ($reply->author_type === 'admin' ? '관리자' : '회원') }}</span>
                    <span class="text-[10px] text-slate-500">{{ $reply->created_at->format('m/d H:i') }}</span>
                </div>
                <p class="mt-3 text-[13px] leading-relaxed text-slate-300 whitespace-pre-wrap">{{ $reply->message }}</p>
            </article>
        @endforeach
    </section>
    @endif

    <section class="rounded-[28px] border border-amber-300/10 bg-amber-500/5 p-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h3 class="text-[15px] font-black text-white">내부 메모</h3>
                <p class="mt-1 text-[12px] text-slate-400">회원에게 노출되지 않는 운영 메모입니다.</p>
            </div>
            <span class="rounded-full bg-amber-500/15 px-2 py-1 text-[10px] font-semibold text-amber-200">{{ $inquiry->internalReplies->count() }}건</span>
        </div>

        <div class="mt-4 space-y-3">
            @forelse($inquiry->internalReplies as $reply)
                <article class="rounded-[24px] border border-amber-300/15 bg-slate-950/60 p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-semibold text-amber-200">
                            {{ $reply->author_type === 'md' ? 'MD 메모' : ($reply->author_type === 'admin' ? '관리자 메모' : '메모') }}
                            {{ $reply->author?->name ?? '' }}
                        </span>
                        <span class="text-[10px] text-slate-500">{{ $reply->created_at->format('m/d H:i') }}</span>
                    </div>
                    <p class="mt-3 text-[13px] leading-relaxed text-slate-300 whitespace-pre-wrap">{{ $reply->message }}</p>
                </article>
            @empty
                <div class="rounded-[24px] border border-dashed border-white/10 bg-slate-950/50 px-4 py-4 text-[12px] text-slate-500">
                    저장된 내부 메모가 없습니다.
                </div>
            @endforelse
        </div>
    </section>

    @if($inquiry->status !== 'closed')
    <form method="POST" action="{{ route('md-dashboard.inquiries.reply', $inquiry) }}" id="reply-form" class="space-y-4 rounded-[28px] border border-white/10 bg-white/5 p-5">
        @csrf
        <div>
            <h3 class="text-[15px] font-black text-white">답변 작성</h3>
            <p class="mt-1 text-[12px] text-slate-400">모바일에서 짧게 입력해도 현장에서 바로 답변이 반영됩니다.</p>
        </div>
        @include('partials.reply-template-picker', [
            'templates' => $replyTemplates,
            'textareaId' => 'md-reply-message',
            'theme' => 'dark',
        ])
        <textarea id="md-reply-message" name="message" rows="5" required class="w-full rounded-2xl border border-white/10 bg-slate-900/70 px-4 py-3 text-[14px] text-white" placeholder="답변 내용을 입력하세요"></textarea>
        <button type="submit" class="w-full rounded-2xl bg-white px-4 py-3 text-[14px] font-black text-slate-950">답변 등록</button>
    </form>

    <form method="POST" action="{{ route('md-dashboard.inquiries.reply', $inquiry) }}" class="space-y-4 rounded-[28px] border border-amber-300/10 bg-amber-500/5 p-5">
        @csrf
        <input type="hidden" name="is_internal" value="1">
        <div>
            <h3 class="text-[15px] font-black text-white">내부 메모 작성</h3>
            <p class="mt-1 text-[12px] text-slate-400">이 메모는 운영용으로만 저장되며 회원 알림과 상태 변경을 발생시키지 않습니다.</p>
        </div>
        <textarea name="message" rows="4" required class="w-full rounded-2xl border border-amber-300/10 bg-slate-900/70 px-4 py-3 text-[14px] text-white" placeholder="내부 메모를 입력하세요"></textarea>
        <button type="submit" class="w-full rounded-2xl bg-amber-500 px-4 py-3 text-[14px] font-black text-white">내부 메모 저장</button>
    </form>
    @endif

    @if($inquiry->status !== 'closed')
    <div class="fixed inset-x-0 bottom-[74px] z-30 px-4 md:hidden">
        <div class="mx-auto max-w-lg rounded-[24px] border border-white/10 bg-slate-950/90 p-2.5 backdrop-blur">
            <a href="#reply-form" class="flex items-center justify-center rounded-[18px] bg-white px-4 py-3 text-[13px] font-black text-slate-950">답변 입력으로 이동</a>
        </div>
    </div>
    @endif
</div>
@endsection
