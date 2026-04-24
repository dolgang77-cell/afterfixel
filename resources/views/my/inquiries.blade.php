@extends('layouts.app')
@section('title', __('inquiry.my_title'))
@section('content')
<div class="px-4 py-6 space-y-4">
    <h1 class="text-[20px] font-extrabold tracking-tight">{{ __('inquiry.my_title') }}</h1>

    @forelse($inquiries as $inq)
    @php
        $timeline = $inq->timelineSteps();
        $currentStep = collect($timeline)->firstWhere('current', true) ?? $timeline[0];
    @endphp
    <div class="card p-4">
        <div class="flex items-center justify-between mb-1 gap-3">
            <span class="text-[13px] font-semibold text-gray-200">{{ $inq->subject }}</span>
            <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold {{ $inq->statusToneClass() }}">{{ $inq->statusLabel }}</span>
        </div>
        <p class="text-[12px] text-gray-500">{{ $inq->target_type === 'club' ? __('nav.club') : __('nav.party') }} · {{ $inq->intent_label }} · {{ $inq->created_at->format('n/j H:i') }}</p>
        <div class="mt-3 rounded-2xl border border-white/[0.05] bg-dark-700/40 px-3 py-3">
            <div class="flex items-center justify-between gap-3">
                <p class="text-[11px] font-semibold text-gray-200">현재 단계</p>
                <span class="text-[11px] font-semibold text-accent">{{ $currentStep['label'] }}</span>
            </div>
            <div class="mt-3 flex items-center gap-1.5">
                @foreach($timeline as $step)
                    <div class="flex min-w-0 flex-1 items-center gap-1.5">
                        <span class="h-2.5 w-2.5 shrink-0 rounded-full {{ $step['completed'] ? 'bg-accent' : 'bg-white/10' }}"></span>
                        @if(!$loop->last)
                            <span class="h-px flex-1 {{ $step['completed'] ? 'bg-accent/60' : 'bg-white/10' }}"></span>
                        @endif
                    </div>
                @endforeach
            </div>
            <p class="mt-2 text-[11px] text-gray-500">{{ $currentStep['description'] }}</p>
        </div>
        @if($inq->budget_text || $inq->visit_date || $inq->party_size)
            <p class="mt-2 text-[11px] text-gray-600">
                {{ $inq->budget_text ?: '예산 미정' }}
                @if($inq->visit_date)
                    · {{ $inq->visit_date->format('n/j') }}
                @endif
                @if($inq->party_size)
                    · {{ $inq->party_size }}명
                @endif
            </p>
        @endif
        <div class="mt-2 flex flex-wrap gap-2 text-[11px] text-gray-500">
            <span>최근 답변 {{ $inq->lastPublicReplyText() }}</span>
            <span>·</span>
            <span>첫 답변 {{ $inq->firstResponseText() }}</span>
        </div>
        <div class="mt-3 flex items-center justify-between gap-3">
            <a href="{{ route('my.inquiries.show', $inq) }}" class="text-[12px] font-semibold text-accent">상세 보기</a>
            @if(data_get($inq, 'reminder_meta.eligible'))
                <form action="{{ route('my.inquiries.reminder', $inq) }}" method="POST">
                    @csrf
                    <button type="submit" class="rounded-full bg-white px-3 py-1.5 text-[11px] font-bold text-slate-950">답변 재알림</button>
                </form>
            @else
                <span class="text-[11px] text-gray-600">{{ data_get($inq, 'reminder_meta.message') }}</span>
            @endif
        </div>
    </div>
    @empty
    <div class="card p-8 text-center text-[13px] text-gray-500">{{ __('inquiry.no_items') }}</div>
    @endforelse
    <div>{{ $inquiries->links() }}</div>
</div>
@endsection
