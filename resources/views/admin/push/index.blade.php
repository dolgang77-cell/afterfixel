@extends('admin.layouts.app')
@section('title', '푸쉬 관리')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">푸쉬 관리</h1>
    <a href="{{ route('admin.push.create') }}" class="px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700">+ 새 발송</a>
</div>

<div class="grid md:grid-cols-3 gap-4 mb-6">
    <a href="{{ route('admin.push.create', ['preset' => 'recent_view_no_inquiry']) }}" class="bg-white rounded-xl shadow-sm p-4 border border-sky-100 hover:border-sky-200 transition-colors">
        <p class="text-xs font-semibold text-sky-700">최근 본 후 미문의</p>
        <p class="mt-2 text-sm font-semibold text-gray-800">최근 확인만 하고 문의하지 않은 사용자 복귀</p>
        <p class="mt-1 text-[11px] text-gray-500">클럽/파티 상세로 직접 복귀</p>
    </a>
    <a href="{{ route('admin.push.create', ['preset' => 'favorite_no_inquiry']) }}" class="bg-white rounded-xl shadow-sm p-4 border border-pink-100 hover:border-pink-200 transition-colors">
        <p class="text-xs font-semibold text-pink-700">찜 후 미문의</p>
        <p class="mt-2 text-sm font-semibold text-gray-800">찜만 하고 상담 시작이 없는 사용자 재유도</p>
        <p class="mt-1 text-[11px] text-gray-500">찜한 원래 상세 페이지로 복귀</p>
    </a>
    <a href="{{ route('admin.push.create', ['preset' => 'inquiry_reply_unread']) }}" class="bg-white rounded-xl shadow-sm p-4 border border-amber-100 hover:border-amber-200 transition-colors">
        <p class="text-xs font-semibold text-amber-700">응답 도착 후 미확인</p>
        <p class="mt-2 text-sm font-semibold text-gray-800">문의 답변 알림을 읽지 않은 사용자 재알림</p>
        <p class="mt-1 text-[11px] text-gray-500">내 문의 상세로 직접 복귀</p>
    </a>
</div>

{{-- 통계 --}}
<div class="grid md:grid-cols-5 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-4"><p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p><p class="text-xs text-gray-500">전체 캠페인</p></div>
    <div class="bg-white rounded-xl shadow-sm p-4"><p class="text-2xl font-bold text-green-600">{{ $stats['sent'] }}</p><p class="text-xs text-gray-500">발송 완료</p></div>
    <div class="bg-white rounded-xl shadow-sm p-4"><p class="text-2xl font-bold text-blue-600">{{ $stats['scheduled'] }}</p><p class="text-xs text-gray-500">예약됨</p></div>
    <div class="bg-white rounded-xl shadow-sm p-4"><p class="text-2xl font-bold text-purple-600">{{ number_format($stats['totalSent']) }}</p><p class="text-xs text-gray-500">총 발송</p></div>
    <div class="bg-white rounded-xl shadow-sm p-4"><p class="text-2xl font-bold text-pink-600">{{ number_format($stats['totalClicked']) }}</p><p class="text-xs text-gray-500">총 클릭</p></div>
</div>

<form method="GET" class="bg-white rounded-xl shadow-sm p-4 mb-6 flex flex-wrap gap-3 items-end">
    <div><label class="block text-xs text-gray-500 mb-1">상태</label>
        <select name="status" class="px-3 py-1.5 border rounded-lg text-sm"><option value="">전체</option>
            @foreach(\App\Models\PushCampaign::$statuses as $v=>$l)<option value="{{ $v }}" {{ request('status')===$v?'selected':'' }}>{{ $l }}</option>@endforeach
        </select></div>
    <div><label class="block text-xs text-gray-500 mb-1">유형</label>
        <select name="type" class="px-3 py-1.5 border rounded-lg text-sm"><option value="">전체</option>
            @foreach(\App\Models\PushCampaign::$types as $v=>$l)<option value="{{ $v }}" {{ request('type')===$v?'selected':'' }}>{{ $l }}</option>@endforeach
        </select></div>
    <button type="submit" class="px-4 py-1.5 bg-gray-800 text-white text-sm rounded-lg">검색</button>
</form>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase"><tr>
            <th class="px-4 py-3 text-left">ID</th><th class="text-left">제목</th><th class="text-center">유형</th><th class="text-center">상태</th><th class="text-center">대상</th><th class="text-center">발송</th><th class="text-center">클릭</th><th class="text-left">발송시각</th><th class="text-right">관리</th>
        </tr></thead>
        <tbody class="divide-y">
            @forelse($campaigns as $c)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-gray-400">{{ $c->id }}</td>
                <td class="px-4 py-3 font-medium text-gray-800 max-w-[200px] truncate">{{ $c->title }}</td>
                <td class="px-4 py-3 text-center">
                    <span class="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs rounded-full">{{ \App\Models\PushCampaign::$types[$c->campaign_type] ?? $c->campaign_type }}</span>
                    @if(data_get($c->target_query, 'retention_preset'))
                        <div class="mt-1">
                            <span class="px-2 py-0.5 bg-amber-50 text-amber-700 text-[11px] rounded-full">{{ \App\Models\PushCampaign::$retentionPresets[data_get($c->target_query, 'retention_preset')] ?? '리텐션' }}</span>
                        </div>
                    @endif
                </td>
                <td class="px-4 py-3 text-center">
                    @php $sc=['draft'=>'bg-gray-200 text-gray-600','scheduled'=>'bg-blue-100 text-blue-700','sending'=>'bg-yellow-100 text-yellow-700','sent'=>'bg-green-100 text-green-700','failed'=>'bg-red-100 text-red-700','cancelled'=>'bg-gray-200 text-gray-400']; @endphp
                    <span class="px-2 py-0.5 rounded-full text-xs {{ $sc[$c->status] ?? '' }}">{{ \App\Models\PushCampaign::$statuses[$c->status] }}</span>
                </td>
                <td class="px-4 py-3 text-center text-gray-500">{{ number_format($c->target_count) }}</td>
                <td class="px-4 py-3 text-center text-gray-500">{{ number_format($c->sent_count) }}</td>
                <td class="px-4 py-3 text-center text-gray-500">{{ number_format($c->clicked_count) }}</td>
                <td class="px-4 py-3 text-gray-500 text-xs">{{ $c->sent_at?->format('m-d H:i') ?? ($c->scheduled_at?->format('m-d H:i 예약') ?? '-') }}</td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('admin.push.show', $c) }}" class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-600 hover:bg-gray-200">상세</a>
                    @if(in_array($c->status, ['draft','scheduled']))
                    <a href="{{ route('admin.push.edit', $c) }}" class="text-xs px-2 py-1 rounded bg-purple-100 text-purple-700 hover:bg-purple-200">수정</a>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="9" class="px-4 py-8 text-center text-gray-400">캠페인이 없습니다.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $campaigns->links() }}</div>
@endsection
