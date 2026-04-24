@extends('admin.layouts.app')
@section('title', '신고 관리')
@section('content')
<h1 class="text-2xl font-bold text-gray-800 mb-6">신고 관리 <span class="text-sm font-normal text-red-500">미처리 {{ $pendingCount }}건</span></h1>
<form method="GET" class="bg-white rounded-xl shadow-sm p-4 mb-6 flex flex-wrap gap-3 items-end">
    <div><label class="block text-xs text-gray-500 mb-1">상태</label><select name="status" class="px-3 py-1.5 border rounded-lg text-sm"><option value="">전체</option><option value="pending" {{ request('status')==='pending'?'selected':'' }}>미처리</option><option value="reviewed" {{ request('status')==='reviewed'?'selected':'' }}>처리됨</option><option value="dismissed" {{ request('status')==='dismissed'?'selected':'' }}>기각</option></select></div>
    <div><label class="block text-xs text-gray-500 mb-1">대상</label><select name="target_type" class="px-3 py-1.5 border rounded-lg text-sm"><option value="">전체</option><option value="community_post" {{ request('target_type')==='community_post'?'selected':'' }}>게시글</option><option value="review" {{ request('target_type')==='review'?'selected':'' }}>후기</option><option value="media" {{ request('target_type')==='media'?'selected':'' }}>이미지</option></select></div>
    <button type="submit" class="px-4 py-1.5 bg-gray-800 text-white text-sm rounded-lg">검색</button>
</form>
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase"><tr>
            <th class="px-4 py-3 text-left">ID</th><th class="text-left">대상</th><th class="text-left">사유</th><th class="text-left">신고자</th><th class="text-center">상태</th><th class="text-left">접수일</th><th class="text-right">관리</th>
        </tr></thead>
        <tbody class="divide-y">
            @forelse($reports as $r)
            <tr class="hover:bg-gray-50 {{ $r->status==='pending'?'bg-red-50':'' }}">
                <td class="px-4 py-3 text-gray-400">{{ $r->id }}</td>
                <td class="px-4 py-3 text-gray-600 text-xs">{{ ['community_post'=>'게시글','review'=>'후기','media'=>'이미지'][$r->target_type] ?? $r->target_type }} #{{ $r->target_id }}</td>
                <td class="px-4 py-3 text-gray-600">{{ \App\Models\Report::$reasons[$r->reason] ?? $r->reason }}</td>
                <td class="px-4 py-3 text-gray-500 text-xs">{{ $r->reporter?->nickname ?? $r->reporter_id }}</td>
                <td class="px-4 py-3 text-center">
                    @php $sc=['pending'=>'bg-red-100 text-red-700','reviewed'=>'bg-green-100 text-green-700','dismissed'=>'bg-gray-200 text-gray-500']; @endphp
                    <span class="px-2 py-0.5 rounded-full text-xs {{ $sc[$r->status]??'' }}">{{ ['pending'=>'미처리','reviewed'=>'처리됨','dismissed'=>'기각'][$r->status] }}</span>
                </td>
                <td class="px-4 py-3 text-gray-400 text-xs">{{ $r->created_at->format('m-d H:i') }}</td>
                <td class="px-4 py-3 text-right space-x-1">
                    @if($r->status==='pending')
                    <form action="{{ route('admin.moderation.reports.review', $r) }}" method="POST" class="inline">@csrf @method('PATCH')<input type="hidden" name="status" value="reviewed"><button class="text-xs px-2 py-1 rounded bg-green-100 text-green-700 hover:bg-green-200">처리</button></form>
                    <form action="{{ route('admin.moderation.reports.review', $r) }}" method="POST" class="inline">@csrf @method('PATCH')<input type="hidden" name="status" value="dismissed"><button class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-600 hover:bg-gray-200">기각</button></form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">신고가 없습니다.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $reports->links() }}</div>
@endsection
