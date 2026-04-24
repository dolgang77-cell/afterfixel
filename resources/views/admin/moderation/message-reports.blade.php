@extends('admin.layouts.app')

@section('title', '메시지 신고 관리')

@section('content')
<h1 class="mb-6 text-2xl font-bold text-gray-800">
    메시지 신고 관리
    <span class="text-sm font-normal text-red-500">미처리 {{ $pendingCount }}건</span>
</h1>

<form method="GET" class="mb-6 flex flex-wrap items-end gap-3 rounded-xl bg-white p-4 shadow-sm">
    <div>
        <label class="mb-1 block text-xs text-gray-500">상태</label>
        <select name="status" class="rounded-lg border px-3 py-1.5 text-sm">
            <option value="">전체</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>미처리</option>
            <option value="reviewed" {{ request('status') === 'reviewed' ? 'selected' : '' }}>처리됨</option>
            <option value="dismissed" {{ request('status') === 'dismissed' ? 'selected' : '' }}>기각</option>
        </select>
    </div>
    <div>
        <label class="mb-1 block text-xs text-gray-500">사유</label>
        <select name="reason" class="rounded-lg border px-3 py-1.5 text-sm">
            <option value="">전체</option>
            @foreach(\App\Models\Report::$reasons as $reasonKey => $reasonLabel)
                <option value="{{ $reasonKey }}" {{ request('reason') === $reasonKey ? 'selected' : '' }}>{{ $reasonLabel }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="rounded-lg bg-gray-800 px-4 py-1.5 text-sm text-white">검색</button>
</form>

<div class="overflow-hidden rounded-xl bg-white shadow-sm">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
            <tr>
                <th class="px-4 py-3 text-left">ID</th>
                <th class="px-4 py-3 text-left">대화</th>
                <th class="px-4 py-3 text-left">메시지</th>
                <th class="px-4 py-3 text-left">신고자 / 대상</th>
                <th class="px-4 py-3 text-left">사유</th>
                <th class="px-4 py-3 text-center">상태</th>
                <th class="px-4 py-3 text-right">관리</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($reports as $report)
                @php
                    $statusClass = [
                        'pending' => 'bg-red-100 text-red-700',
                        'reviewed' => 'bg-green-100 text-green-700',
                        'dismissed' => 'bg-gray-200 text-gray-600',
                    ][$report->status] ?? 'bg-gray-100 text-gray-600';
                @endphp
                <tr class="align-top {{ $report->status === 'pending' ? 'bg-red-50/60' : 'hover:bg-gray-50' }}">
                    <td class="px-4 py-3 text-xs text-gray-400">{{ $report->id }}</td>
                    <td class="px-4 py-3 text-xs text-gray-500">
                        <div>#{{ $report->conversation_id ?: '-' }}</div>
                        <div class="mt-1">메시지 #{{ $report->message_id ?: '-' }}</div>
                        <div class="mt-1">{{ $report->created_at?->format('m-d H:i') }}</div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="max-w-xs rounded-xl bg-gray-50 px-3 py-2 text-xs leading-relaxed text-gray-700">
                            {{ $report->snapshot_body ?: '원문 스냅샷 없음' }}
                        </div>
                        @if($report->detail)
                            <p class="mt-2 text-xs text-gray-500">신고 메모: {{ $report->detail }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-600">
                        <div>신고자: {{ $report->reporter?->nickname ?? $report->reporter?->name ?? ('#' . $report->reporter_id) }}</div>
                        <div class="mt-1">대상자: {{ $report->reportedUser?->nickname ?? $report->reportedUser?->name ?? ($report->reported_user_id ? '#' . $report->reported_user_id : '-') }}</div>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-700">{{ \App\Models\Report::$reasons[$report->reason] ?? $report->reason }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="rounded-full px-2 py-0.5 text-xs {{ $statusClass }}">
                            {{ ['pending' => '미처리', 'reviewed' => '처리됨', 'dismissed' => '기각'][$report->status] ?? $report->status }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        @if($report->status === 'pending')
                            <div class="space-y-2">
                                <form action="{{ route('admin.moderation.message-reports.review', $report) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="reviewed">
                                    <button class="rounded bg-green-100 px-2 py-1 text-xs text-green-700 hover:bg-green-200">처리</button>
                                </form>
                                <form action="{{ route('admin.moderation.message-reports.review', $report) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="dismissed">
                                    <button class="rounded bg-gray-100 px-2 py-1 text-xs text-gray-600 hover:bg-gray-200">기각</button>
                                </form>
                                @if($report->reported_user_id)
                                    <form action="{{ route('admin.moderation.message-reports.review', $report) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="reviewed">
                                        <input type="hidden" name="action_type" value="restrict_write">
                                        <input type="hidden" name="duration" value="3">
                                        <input type="hidden" name="note" value="메시지 신고 검토 후 글쓰기 제한">
                                        <button class="rounded bg-amber-100 px-2 py-1 text-xs text-amber-700 hover:bg-amber-200">글제한 3일</button>
                                    </form>
                                    <form action="{{ route('admin.moderation.message-reports.review', $report) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="reviewed">
                                        <input type="hidden" name="action_type" value="suspend">
                                        <input type="hidden" name="duration" value="7">
                                        <input type="hidden" name="note" value="메시지 신고 검토 후 정지">
                                        <button class="rounded bg-red-100 px-2 py-1 text-xs text-red-700 hover:bg-red-200">정지 7일</button>
                                    </form>
                                @endif
                            </div>
                        @else
                            <p class="text-xs text-gray-400">처리 완료</p>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-400">메시지 신고가 없습니다.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $reports->links() }}
</div>
@endsection
