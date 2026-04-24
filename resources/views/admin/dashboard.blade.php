@extends('admin.layouts.app')
@section('title', '대시보드')
@section('page_type', 'admin_dashboard')

@section('content')
<h1 class="text-2xl font-bold text-gray-800 mb-6">대시보드</h1>

{{-- 핵심 통계 --}}
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
    @php
        $cards = [
            ['label' => '전체 클럽', 'value' => $stats['clubs'], 'sub' => "활성 {$stats['active_clubs']} · 영업중 {$stats['open_clubs_now']}", 'color' => 'purple'],
            ['label' => '전체 파티', 'value' => $stats['parties'], 'sub' => "예정 {$stats['upcoming_parties']} · 오늘 {$stats['today_parties']}", 'color' => 'pink'],
            ['label' => '게시글', 'value' => $stats['posts'], 'sub' => "숨김 {$stats['hidden_posts']}", 'color' => 'blue'],
            ['label' => '신고 대기', 'value' => $stats['reported_posts'], 'sub' => '검수 필요', 'color' => 'red'],
            ['label' => '오늘 클릭', 'value' => $stats['today_clicks'], 'sub' => "주간 " . number_format($stats['week_clicks']), 'color' => 'green'],
            ['label' => '추천 생성', 'value' => $stats['recommendations'], 'sub' => "오늘 {$stats['today_recommendations']}", 'color' => 'yellow'],
        ];
    @endphp
    @foreach($cards as $card)
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-{{ $card['color'] }}-500">
            <p class="text-xs text-gray-500">{{ $card['label'] }}</p>
            <p class="text-xl font-bold text-gray-800 mt-1">{{ number_format($card['value']) }}</p>
            <p class="text-[10px] text-gray-400 mt-1">{{ $card['sub'] }}</p>
        </div>
    @endforeach
</div>

{{-- 문의 인박스 --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="font-semibold text-gray-800">문의 인박스</h2>
            <p class="text-xs text-gray-500 mt-1">오늘 우선 처리해야 할 문의 큐를 먼저 확인합니다.</p>
        </div>
        <a href="{{ route('admin.inquiries.index') }}" class="text-sm text-purple-600 hover:underline">문의 관리로 이동</a>
    </div>

    <div class="grid md:grid-cols-4 gap-3">
        <a href="{{ route('admin.inquiries.index', ['queue' => 'unanswered']) }}" class="rounded-xl border border-rose-200 bg-rose-50 p-4 hover:border-rose-300 transition-colors" data-track-event="admin_inbox_item_open" data-track-context="queue_card" data-track-label="미응답" data-track-meta='@json(["queue" => "unanswered"])'>
            <p class="text-xs font-semibold text-rose-700">미응답</p>
            <p class="mt-2 text-2xl font-bold text-rose-900">{{ number_format($inboxSummary['unanswered_count']) }}</p>
            <p class="mt-1 text-[11px] text-rose-700">아직 첫 답변이 없는 문의</p>
        </a>
        <a href="{{ route('admin.inquiries.index', ['queue' => 'delayed']) }}" class="rounded-xl border border-amber-200 bg-amber-50 p-4 hover:border-amber-300 transition-colors" data-track-event="admin_inbox_item_open" data-track-context="queue_card" data-track-label="응답 지연" data-track-meta='@json(["queue" => "delayed"])'>
            <p class="text-xs font-semibold text-amber-700">응답 지연</p>
            <p class="mt-2 text-2xl font-bold text-amber-900">{{ number_format($inboxSummary['delayed_count']) }}</p>
            <p class="mt-1 text-[11px] text-amber-700">30분 이상 답변이 없는 문의</p>
        </a>
        <a href="{{ route('admin.inquiries.index', ['queue' => 'quote_needed']) }}" class="rounded-xl border border-sky-200 bg-sky-50 p-4 hover:border-sky-300 transition-colors" data-track-event="admin_inbox_item_open" data-track-context="queue_card" data-track-label="견적 필요" data-track-meta='@json(["queue" => "quote_needed"])'>
            <p class="text-xs font-semibold text-sky-700">견적 필요</p>
            <p class="mt-2 text-2xl font-bold text-sky-900">{{ number_format($inboxSummary['quote_needed_count']) }}</p>
            <p class="mt-1 text-[11px] text-sky-700">견적/예약 요청 기반 문의</p>
        </a>
        <a href="{{ route('admin.inquiries.index', ['queue' => 'confirmation_waiting']) }}" class="rounded-xl border border-violet-200 bg-violet-50 p-4 hover:border-violet-300 transition-colors" data-track-event="admin_inbox_item_open" data-track-context="queue_card" data-track-label="확정 대기" data-track-meta='@json(["queue" => "confirmation_waiting"])'>
            <p class="text-xs font-semibold text-violet-700">확정 대기</p>
            <p class="mt-2 text-2xl font-bold text-violet-900">{{ number_format($inboxSummary['confirmation_waiting_count']) }}</p>
            <p class="mt-1 text-[11px] text-violet-700">답변 완료 후 확인 대기 상태</p>
        </a>
    </div>

    <div class="mt-5">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-gray-800">우선 확인 문의</h3>
            <span class="text-xs text-gray-400">lead inbox 기준 상위 5건</span>
        </div>
        <div class="space-y-2">
            @forelse($inboxSummary['priority_items'] as $inq)
                <a href="{{ route('admin.inquiries.show', $inq) }}" class="flex items-center justify-between gap-3 rounded-xl border border-gray-100 px-4 py-3 hover:bg-gray-50" data-track-event="admin_inbox_item_open" data-track-context="priority_item" data-track-target-type="inquiry" data-track-target-id="{{ $inq->id }}" data-track-label="{{ $inq->subject }}">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $inq->subject }}</p>
                        <p class="mt-1 text-xs text-gray-500">{{ $inq->user?->name ?? '비회원' }} · {{ $inq->assignedMd?->display_name ?? '미배정' }} · 최근 답변 {{ $inq->lastPublicReplyText() }}</p>
                    </div>
                    <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $inq->status === 'pending' ? 'bg-rose-100 text-rose-700' : ($inq->status === 'answered' ? 'bg-violet-100 text-violet-700' : 'bg-slate-100 text-slate-700') }}">{{ $inq->statusLabel }}</span>
                </a>
            @empty
                <p class="text-sm text-gray-400 text-center py-4">열려 있는 문의가 없습니다.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- 주간 추이 차트 --}}
<div class="grid lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="font-semibold text-gray-800 mb-3">주간 클릭 추이</h2>
        @if(!empty($weeklyClicks))
            <div class="flex gap-1 items-end h-24">
                @php $maxClick = max($weeklyClicks) ?: 1; @endphp
                @foreach($weeklyClicks as $date => $total)
                    <div class="flex flex-col items-center flex-1 min-w-0" title="{{ $date }}: {{ $total }}건">
                        <span class="text-[10px] text-gray-400 mb-1">{{ $total }}</span>
                        <div class="w-full bg-purple-400 rounded-t transition-all" style="height: {{ max(4, ($total / $maxClick) * 80) }}px"></div>
                        <span class="text-[9px] text-gray-400 mt-1">{{ substr($date, 5) }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-400 text-center py-6">데이터 없음</p>
        @endif
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="font-semibold text-gray-800 mb-3">주간 추천 추이</h2>
        @if(!empty($weeklyRecs))
            <div class="flex gap-1 items-end h-24">
                @php $maxRec = max($weeklyRecs) ?: 1; @endphp
                @foreach($weeklyRecs as $date => $total)
                    <div class="flex flex-col items-center flex-1 min-w-0" title="{{ $date }}: {{ $total }}건">
                        <span class="text-[10px] text-gray-400 mb-1">{{ $total }}</span>
                        <div class="w-full bg-pink-400 rounded-t transition-all" style="height: {{ max(4, ($total / $maxRec) * 80) }}px"></div>
                        <span class="text-[9px] text-gray-400 mt-1">{{ substr($date, 5) }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-400 text-center py-6">데이터 없음</p>
        @endif
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    {{-- 신고 게시글 --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-gray-800">신고된 게시글</h2>
            <a href="{{ route('admin.posts.index', ['filter' => 'reported']) }}" class="text-sm text-purple-600 hover:underline">전체보기</a>
        </div>
        @forelse($recentPosts as $post)
            <div class="flex items-center justify-between py-2 border-b last:border-0">
                <div class="min-w-0">
                    <p class="text-sm text-gray-700 truncate">{{ $post->title }}</p>
                    <p class="text-xs text-gray-400">{{ $post->nickname }} · {{ $post->type_text }}</p>
                </div>
                <span class="ml-3 shrink-0 px-2 py-0.5 bg-red-100 text-red-600 text-xs rounded-full">신고 {{ $post->report_count }}</span>
            </div>
        @empty
            <p class="text-sm text-gray-400 text-center py-4">신고된 게시글이 없습니다.</p>
        @endforelse
    </div>

    {{-- 최근 관리 로그 --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-gray-800">최근 관리 로그</h2>
            <a href="{{ route('admin.logs.index') }}" class="text-sm text-purple-600 hover:underline">전체보기</a>
        </div>
        @forelse($recentLogs as $log)
            <div class="flex items-center justify-between py-2 border-b last:border-0">
                <div class="min-w-0">
                    <p class="text-sm text-gray-700">
                        <span class="font-medium">{{ $log->user?->name ?? '시스템' }}</span>
                        <span class="text-gray-400">{{ $log->action }}</span>
                        <span class="text-gray-500">{{ $log->target_type }}#{{ $log->target_id }}</span>
                    </p>
                </div>
                <span class="ml-3 shrink-0 text-xs text-gray-400">{{ $log->created_at->diffForHumans() }}</span>
            </div>
        @empty
            <p class="text-sm text-gray-400 text-center py-4">관리 로그가 없습니다.</p>
        @endforelse
    </div>

    {{-- 최근 알림 --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-gray-800">최근 알림 발송</h2>
            <a href="{{ route('admin.logs.notifications') }}" class="text-sm text-purple-600 hover:underline">전체보기</a>
        </div>
        @forelse($recentNotifications as $notif)
            <div class="py-2 border-b last:border-0">
                <p class="text-sm text-gray-700 truncate">{{ $notif->title }}</p>
                <div class="flex items-center gap-2 mt-0.5">
                    @php
                        $typeColors = [
                            'party_reminder'          => 'bg-blue-100 text-blue-600',
                            'new_party_match'         => 'bg-green-100 text-green-600',
                            'tonight_recommendation'  => 'bg-purple-100 text-purple-600',
                        ];
                    @endphp
                    <span class="px-1.5 py-0.5 rounded text-[10px] {{ $typeColors[$notif->type] ?? 'bg-gray-100 text-gray-600' }}">{{ $notif->type }}</span>
                    <span class="text-xs text-gray-400">{{ $notif->created_at->diffForHumans() }}</span>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-400 text-center py-4">발송된 알림이 없습니다.</p>
        @endforelse
    </div>
</div>

{{-- 바로가기 --}}
<div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-3">
    <a href="{{ route('admin.clubs.create') }}" class="bg-white rounded-xl shadow-sm p-4 text-center hover:shadow-md transition-shadow">
        <span class="text-2xl">🏢</span>
        <p class="text-sm font-medium text-gray-700 mt-2">클럽 등록</p>
    </a>
    <a href="{{ route('admin.parties.create') }}" class="bg-white rounded-xl shadow-sm p-4 text-center hover:shadow-md transition-shadow">
        <span class="text-2xl">🎉</span>
        <p class="text-sm font-medium text-gray-700 mt-2">파티 등록</p>
    </a>
    <a href="{{ route('admin.exposure.index') }}" class="bg-white rounded-xl shadow-sm p-4 text-center hover:shadow-md transition-shadow">
        <span class="text-2xl">📊</span>
        <p class="text-sm font-medium text-gray-700 mt-2">노출 관리</p>
    </a>
    <a href="{{ route('admin.posts.index', ['filter' => 'reported']) }}" class="bg-white rounded-xl shadow-sm p-4 text-center hover:shadow-md transition-shadow">
        <span class="text-2xl">🚨</span>
        <p class="text-sm font-medium text-gray-700 mt-2">신고 검수</p>
    </a>
</div>
@endsection
