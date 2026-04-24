@extends('admin.layouts.app')
@section('title', '문의 관리')
@section('content')
<h1 class="text-2xl font-bold text-gray-800 mb-6">문의 관리</h1>

<div class="grid md:grid-cols-4 gap-3 mb-6">
    <a href="{{ route('admin.inquiries.index', ['queue' => 'unanswered']) }}" class="bg-white rounded-xl shadow-sm p-4 border border-rose-100 hover:border-rose-200 transition-colors">
        <p class="text-xs font-semibold text-rose-700">미응답</p>
        <p class="mt-2 text-2xl font-bold text-rose-900">{{ number_format($inboxSummary['unanswered_count']) }}</p>
        <p class="mt-1 text-[11px] text-gray-500">첫 답변이 없는 문의</p>
    </a>
    <a href="{{ route('admin.inquiries.index', ['queue' => 'delayed']) }}" class="bg-white rounded-xl shadow-sm p-4 border border-amber-100 hover:border-amber-200 transition-colors">
        <p class="text-xs font-semibold text-amber-700">응답 지연</p>
        <p class="mt-2 text-2xl font-bold text-amber-900">{{ number_format($inboxSummary['delayed_count']) }}</p>
        <p class="mt-1 text-[11px] text-gray-500">30분 이상 대기</p>
    </a>
    <a href="{{ route('admin.inquiries.index', ['queue' => 'quote_needed']) }}" class="bg-white rounded-xl shadow-sm p-4 border border-sky-100 hover:border-sky-200 transition-colors">
        <p class="text-xs font-semibold text-sky-700">견적 필요</p>
        <p class="mt-2 text-2xl font-bold text-sky-900">{{ number_format($inboxSummary['quote_needed_count']) }}</p>
        <p class="mt-1 text-[11px] text-gray-500">견적/예약 요청 기반 문의</p>
    </a>
    <a href="{{ route('admin.inquiries.index', ['queue' => 'confirmation_waiting']) }}" class="bg-white rounded-xl shadow-sm p-4 border border-violet-100 hover:border-violet-200 transition-colors">
        <p class="text-xs font-semibold text-violet-700">확정 대기</p>
        <p class="mt-2 text-2xl font-bold text-violet-900">{{ number_format($inboxSummary['confirmation_waiting_count']) }}</p>
        <p class="mt-1 text-[11px] text-gray-500">답변 완료 후 후속 확인</p>
    </a>
</div>

<div class="grid md:grid-cols-3 gap-3 mb-6">
    <a href="{{ route('admin.inquiries.index', ['queue' => 'sla_10']) }}" class="bg-white rounded-xl shadow-sm p-4 border border-sky-100 hover:border-sky-200 transition-colors">
        <p class="text-xs font-semibold text-sky-700">SLA 10분</p>
        <p class="mt-2 text-2xl font-bold text-sky-900">{{ number_format($inboxSummary['sla_10_count']) }}</p>
        <p class="mt-1 text-[11px] text-gray-500">10분 이상 첫 답변 대기</p>
    </a>
    <a href="{{ route('admin.inquiries.index', ['queue' => 'sla_30']) }}" class="bg-white rounded-xl shadow-sm p-4 border border-amber-100 hover:border-amber-200 transition-colors">
        <p class="text-xs font-semibold text-amber-700">SLA 30분</p>
        <p class="mt-2 text-2xl font-bold text-amber-900">{{ number_format($inboxSummary['sla_30_count']) }}</p>
        <p class="mt-1 text-[11px] text-gray-500">30분 이상 첫 답변 대기</p>
    </a>
    <a href="{{ route('admin.inquiries.index', ['queue' => 'sla_60']) }}" class="bg-white rounded-xl shadow-sm p-4 border border-rose-100 hover:border-rose-200 transition-colors">
        <p class="text-xs font-semibold text-rose-700">SLA 60분</p>
        <p class="mt-2 text-2xl font-bold text-rose-900">{{ number_format($inboxSummary['sla_60_count']) }}</p>
        <p class="mt-1 text-[11px] text-gray-500">60분 이상 첫 답변 대기</p>
    </a>
</div>

<form method="GET" class="bg-white rounded-xl shadow-sm p-4 mb-6 flex flex-wrap gap-3 items-end">
    <input type="hidden" name="queue" value="{{ request('queue') }}">
    <div><label class="block text-xs text-gray-500 mb-1">상태</label>
        <select name="status" class="px-3 py-1.5 border rounded-lg text-sm"><option value="">전체</option>
            @foreach(\App\Models\Inquiry::$statuses as $v => $l)<option value="{{ $v }}" {{ request('status')===$v?'selected':'' }}>{{ $l }}</option>@endforeach
        </select></div>
    <div><label class="block text-xs text-gray-500 mb-1">문의 유형</label>
        <select name="intent_type" class="px-3 py-1.5 border rounded-lg text-sm"><option value="">전체</option>
            @foreach(\App\Models\Inquiry::$intentLabels as $v => $l)<option value="{{ $v }}" {{ request('intent_type')===$v?'selected':'' }}>{{ $l }}</option>@endforeach
        </select></div>
    <div><label class="block text-xs text-gray-500 mb-1">대상</label>
        <select name="target_type" class="px-3 py-1.5 border rounded-lg text-sm"><option value="">전체</option><option value="club" {{ request('target_type')==='club'?'selected':'' }}>클럽</option><option value="party" {{ request('target_type')==='party'?'selected':'' }}>파티</option></select></div>
    <div><label class="block text-xs text-gray-500 mb-1">담당 MD</label>
        <select name="md_id" class="px-3 py-1.5 border rounded-lg text-sm"><option value="">전체</option>
            @foreach($mds as $id=>$name)<option value="{{ $id }}" {{ request('md_id')==$id?'selected':'' }}>{{ $name }}</option>@endforeach
        </select></div>
    <button type="submit" class="px-4 py-1.5 bg-gray-800 text-white text-sm rounded-lg">검색</button>
    @if(request()->filled('queue') || request()->filled('status') || request()->filled('intent_type') || request()->filled('target_type') || request()->filled('md_id'))
        <a href="{{ route('admin.inquiries.index') }}" class="px-4 py-1.5 bg-gray-100 text-gray-700 text-sm rounded-lg">초기화</a>
    @endif
</form>
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase"><tr>
            <th class="px-4 py-3 text-left">ID</th><th class="text-left">제목</th><th class="text-left">회원</th><th class="text-left">대상</th><th class="text-left">담당 MD</th><th class="text-center">상태</th><th class="text-left">응답 상황</th><th class="text-left">접수일</th><th class="text-right">관리</th>
        </tr></thead>
        <tbody class="divide-y">
            @forelse($inquiries as $inq)
            @php $slaLabel = $inq->slaLabel(); @endphp
            <tr class="hover:bg-gray-50 {{ $inq->slaLevel() === 'critical' ? 'bg-rose-50' : ($inq->slaLevel() === 'warning' ? 'bg-amber-50' : ($inq->status === 'pending' ? 'bg-red-50' : '')) }}">
                <td class="px-4 py-3 text-gray-400">{{ $inq->id }}</td>
                <td class="px-4 py-3 font-medium text-gray-800 max-w-[180px] truncate">
                    <div>{{ $inq->subject }}</div>
                    <div class="mt-1 flex flex-wrap items-center gap-1 text-[11px] font-medium text-gray-400">
                        <span>{{ $inq->intent_label }}</span>
                        <span>· 우선도 {{ $inq->leadPriorityScore() }}</span>
                    </div>
                </td>
                <td class="px-4 py-3 text-gray-600 text-xs">{{ $inq->user?->name ?? '-' }}</td>
                <td class="px-4 py-3 text-gray-500 text-xs">{{ $inq->target_type==='club'?'클럽':'파티' }} #{{ $inq->target_id }}</td>
                <td class="px-4 py-3 text-gray-500 text-xs">{{ $inq->assignedMd?->display_name ?? '미배정' }}</td>
                <td class="px-4 py-3 text-center">
                    @php $sc = ['pending'=>'bg-red-100 text-red-700','in_progress'=>'bg-sky-100 text-sky-700','answered'=>'bg-green-100 text-green-700','reservation_confirmed'=>'bg-violet-100 text-violet-700','consultation_completed'=>'bg-cyan-100 text-cyan-700','closed'=>'bg-gray-200 text-gray-500','hidden'=>'bg-yellow-100 text-yellow-700']; @endphp
                    <span class="px-2 py-0.5 rounded-full text-xs {{ $sc[$inq->status]??'' }}">{{ \App\Models\Inquiry::$statuses[$inq->status] ?? $inq->status }}</span>
                </td>
                <td class="px-4 py-3 text-gray-500 text-xs">
                    <div>첫 답변 {{ $inq->firstResponseText() }}</div>
                    <div class="mt-1 text-gray-400">
                        최근 답변 {{ $inq->lastPublicReplyText() }}
                    </div>
                    @if($slaLabel)
                        <div class="mt-2">
                            <span class="inline-flex rounded-full px-2 py-0.5 font-semibold {{ $inq->slaToneClass('light') }}">{{ $slaLabel }}</span>
                        </div>
                    @else
                        <div class="mt-2 text-[11px] text-gray-400">SLA 정상</div>
                    @endif
                </td>
                <td class="px-4 py-3 text-gray-400 text-xs">{{ $inq->created_at->format('m-d H:i') }}</td>
                <td class="px-4 py-3 text-right"><a href="{{ route('admin.inquiries.show', $inq) }}" class="text-xs px-2 py-1 rounded bg-purple-100 text-purple-700 hover:bg-purple-200">상세</a></td>
            </tr>
            @empty
            <tr><td colspan="9" class="px-4 py-8 text-center text-gray-400">문의가 없습니다.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $inquiries->links() }}</div>
@endsection
