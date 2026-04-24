@extends('layouts.app')
@section('title', __('inquiry.detail'))
@section('page_type', 'my_inquiry_detail')
@section('content')
@php
    $timeline = $inquiry->timelineSteps();
@endphp
<div class="px-4 py-6 space-y-4">
    <div class="flex items-center gap-3 mb-2">
        <a href="{{ route('my.inquiries') }}" class="text-gray-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg></a>
        <h1 class="text-[18px] font-bold text-white">{{ __('inquiry.detail') }}</h1>
    </div>

    <div class="card p-4" data-track-view-event="inquiry_timeline_view" data-track-target-type="inquiry" data-track-target-id="{{ $inquiry->id }}" data-track-context="status_timeline">
        <div class="mb-4 flex flex-wrap items-center gap-2">
            <span class="rounded-full px-3 py-1 text-[11px] font-semibold {{ $inquiry->statusToneClass() }}">{{ $inquiry->statusLabel }}</span>
            @if($inquiry->assignedMd)
                <span class="rounded-full bg-white/5 px-3 py-1 text-[11px] font-semibold text-gray-300">{{ $inquiry->assignedMd->display_name }} 담당</span>
            @endif
            <span class="rounded-full bg-white/5 px-3 py-1 text-[11px] font-semibold text-gray-400">최초 문의 {{ $inquiry->created_at->diffForHumans() }}</span>
        </div>

        <h2 class="text-[15px] font-bold text-white mb-2">{{ $inquiry->subject }}</h2>
        <p class="text-[12px] text-gray-500 mb-3">{{ $inquiry->target_type === 'club' ? __('nav.club') : __('nav.party') }} · {{ $inquiry->intent_label }} · {{ $inquiry->created_at->format('Y-m-d H:i') }}</p>
        <p class="text-[13px] text-gray-400 leading-relaxed whitespace-pre-wrap">{{ $inquiry->message }}</p>
        @if($inquiry->visit_date || $inquiry->party_size || $inquiry->budget_text || $inquiry->visit_time_slot_label || $inquiry->gender_mix)
        <div class="mt-3 flex flex-wrap gap-4 text-[12px] text-gray-500">
            @if($inquiry->visit_date)<span>{{ __('inquiry.visit_date') }} {{ $inquiry->visit_date->format('n/j') }}</span>@endif
            @if($inquiry->party_size)<span>{{ __('inquiry.party_size_label') }} {{ $inquiry->party_size }}명</span>@endif
            @if($inquiry->budget_text)<span>예산 {{ $inquiry->budget_text }}</span>@endif
            @if($inquiry->visit_time_slot_label)<span>도착 {{ $inquiry->visit_time_slot_label }}</span>@endif
            @if($inquiry->gender_mix)<span>구성 {{ $inquiry->gender_mix }}</span>@endif
        </div>
        @endif
        @if($inquiry->special_request)
        <div class="mt-3 rounded-2xl bg-dark-700/70 px-3 py-3 text-[12px] text-gray-400">
            <span class="font-semibold text-gray-300">추가 요청</span><br>
            <span class="whitespace-pre-wrap">{{ $inquiry->special_request }}</span>
        </div>
        @endif
        <div class="mt-4 grid grid-cols-2 gap-2">
            <div class="rounded-2xl border border-white/[0.06] bg-dark-700/50 px-3 py-3">
                <p class="text-[11px] text-gray-500">최근 답변</p>
                <p class="mt-1 text-[13px] font-semibold text-white">{{ $inquiry->lastPublicReplyText() }}</p>
            </div>
            <div class="rounded-2xl border border-white/[0.06] bg-dark-700/50 px-3 py-3">
                <p class="text-[11px] text-gray-500">첫 답변 속도</p>
                <p class="mt-1 text-[13px] font-semibold text-white">{{ $inquiry->firstResponseText() }}</p>
            </div>
        </div>
        <div class="mt-4 flex items-center justify-between gap-3 rounded-2xl bg-dark-700/60 px-3 py-3">
            <div>
                <p class="text-[12px] font-semibold text-gray-200">답변 재알림</p>
                <p class="mt-1 text-[11px] text-gray-500">{{ data_get($inquiry, 'reminder_meta.message') }}</p>
            </div>
            @if(data_get($inquiry, 'reminder_meta.eligible'))
                <form action="{{ route('my.inquiries.reminder', $inquiry) }}" method="POST" data-track-event="inquiry_reminder_click" data-track-trigger="submit" data-track-target-type="inquiry" data-track-target-id="{{ $inquiry->id }}" data-track-context="reminder">
                    @csrf
                    <button type="submit" class="rounded-full bg-white px-3 py-2 text-[11px] font-bold text-slate-950">재알림 보내기</button>
                </form>
            @endif
        </div>
    </div>

    <div class="card p-4">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-[14px] font-bold text-white">문의 진행 상태</p>
                <p class="mt-1 text-[11px] text-gray-500">현재 어디까지 진행됐는지 단계별로 확인할 수 있습니다.</p>
            </div>
            <span class="text-[11px] font-semibold text-accent">{{ $inquiry->currentTimelineStepLabel() }}</span>
        </div>

        <div class="mt-4 space-y-3">
            @foreach($timeline as $step)
                <div class="flex gap-3">
                    <div class="flex w-5 flex-col items-center">
                        <span class="mt-1 h-3.5 w-3.5 rounded-full {{ $step['completed'] ? 'bg-accent shadow-glow-sm' : 'bg-white/10' }}"></span>
                        @if(!$loop->last)
                            <span class="mt-1 w-px flex-1 {{ $step['completed'] ? 'bg-accent/50' : 'bg-white/10' }}"></span>
                        @endif
                    </div>
                    <div class="flex-1 rounded-2xl border px-3 py-3 {{ $step['current'] ? 'border-accent/30 bg-accent/5' : 'border-white/[0.06] bg-dark-700/30' }}">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-[12px] font-semibold {{ $step['current'] || $step['completed'] ? 'text-white' : 'text-gray-400' }}">{{ $step['label'] }}</p>
                            @if($step['at'])
                                <span class="text-[10px] text-gray-500">{{ $step['at']->format('n/j H:i') }}</span>
                            @endif
                        </div>
                        <p class="mt-1 text-[11px] {{ $step['current'] ? 'text-gray-300' : 'text-gray-500' }}">{{ $step['description'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- 대화 내역 --}}
    <div class="px-1">
        <p class="text-[13px] font-bold text-white">대화 내역</p>
        <p class="mt-1 text-[11px] text-gray-500">상담이 이어지는 공간입니다. 추가 정보가 생기면 아래에서 바로 남기면 됩니다.</p>
    </div>
    @foreach($inquiry->publicReplies as $reply)
    <div class="card p-4 {{ $reply->author_type === 'user' ? '' : 'border-l-2 border-accent' }}">
        <div class="flex justify-between text-[11px] text-gray-500 mb-1">
            <span class="font-semibold {{ $reply->author_type !== 'user' ? 'text-accent' : 'text-gray-300' }}">{{ $reply->author_type === 'md' ? __('inquiry.md_reply') : ($reply->author_type === 'admin' ? __('inquiry.admin_reply') : __('inquiry.my_message')) }}</span>
            <span>{{ $reply->created_at->format('n/j H:i') }}</span>
        </div>
        <p class="text-[13px] text-gray-400 leading-relaxed whitespace-pre-wrap">{{ $reply->message }}</p>
    </div>
    @endforeach

    {{-- 추가 메시지 --}}
    @if($inquiry->status !== 'closed')
    <form action="{{ route('my.inquiries.message', $inquiry) }}" method="POST" class="card p-4 space-y-3" data-track-event="inquiry_message_add" data-track-trigger="submit" data-track-target-type="inquiry" data-track-target-id="{{ $inquiry->id }}" data-track-context="detail_reply">
        @csrf
        <div>
            <p class="text-[13px] font-bold text-white">추가 메시지 보내기</p>
            <p class="mt-1 text-[11px] text-gray-500">인원 변경, 도착 시간, 예산 변경 같은 업데이트를 짧게 남기세요.</p>
        </div>
        <textarea name="message" rows="3" required class="w-full bg-dark-700 border border-white/[0.06] rounded-xl px-3 py-3 text-[13px] text-white placeholder-gray-600 focus:border-accent focus:outline-none" placeholder="예: 인원이 4명으로 변경됐고 11시쯤 도착 예정입니다."></textarea>
        <button type="submit" class="btn-primary w-full py-3 rounded-2xl text-[13px] font-bold text-white">{{ __('inquiry.send') }}</button>
    </form>
    @else
    <div class="text-center text-[12px] text-gray-600 py-2">{{ __('inquiry.closed_msg') }}</div>
    @endif
</div>
@endsection
