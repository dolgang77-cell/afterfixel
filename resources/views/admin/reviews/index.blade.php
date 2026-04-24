@extends('admin.layouts.app')
@section('title', '후기 관리')
@section('content')
<h1 class="text-2xl font-bold text-gray-800 mb-6">후기 관리</h1>
<form method="GET" class="bg-white rounded-xl shadow-sm p-4 mb-6 flex flex-wrap gap-3 items-end">
    <div><label class="block text-xs text-gray-500 mb-1">검색</label><input type="text" name="search" value="{{ request('search') }}" placeholder="내용" class="px-3 py-1.5 border rounded-lg text-sm w-40"></div>
    <div><label class="block text-xs text-gray-500 mb-1">대상</label>
        <select name="target_type" class="px-3 py-1.5 border rounded-lg text-sm"><option value="">전체</option><option value="club" {{ request('target_type')==='club'?'selected':'' }}>클럽</option><option value="party" {{ request('target_type')==='party'?'selected':'' }}>파티</option></select></div>
    <div><label class="block text-xs text-gray-500 mb-1">필터</label>
        <select name="filter" class="px-3 py-1.5 border rounded-lg text-sm"><option value="">전체</option><option value="reported" {{ request('filter')==='reported'?'selected':'' }}>신고됨</option><option value="hidden" {{ request('filter')==='hidden'?'selected':'' }}>숨김</option></select></div>
    <button type="submit" class="px-4 py-1.5 bg-gray-800 text-white text-sm rounded-lg">검색</button>
</form>
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase"><tr>
            <th class="px-4 py-3 text-left">ID</th><th class="text-left">작성자</th><th class="text-left">대상</th><th class="text-left">내용</th><th class="text-center">평점</th><th class="text-center">신고</th><th class="text-center">상태</th><th class="text-left">작성일</th><th class="text-right">관리</th>
        </tr></thead>
        <tbody class="divide-y">
            @forelse($reviews as $r)
            <tr class="hover:bg-gray-50 {{ $r->report_count >= 3 ? 'bg-red-50' : '' }}">
                <td class="px-4 py-3 text-gray-400">{{ $r->id }}</td>
                <td class="px-4 py-3 text-gray-700">{{ $r->user?->name ?? '-' }}</td>
                <td class="px-4 py-3 text-xs text-gray-500">{{ $r->target_type==='club'?'클럽':'파티' }} #{{ $r->target_id }}</td>
                <td class="px-4 py-3 text-gray-600 max-w-[200px] truncate">{{ $r->content }}</td>
                <td class="px-4 py-3 text-center">{{ $r->rating ?? '-' }}</td>
                <td class="px-4 py-3 text-center">@if($r->report_count>0)<span class="px-2 py-0.5 bg-red-100 text-red-600 text-xs rounded-full">{{ $r->report_count }}</span>@else<span class="text-gray-300">0</span>@endif</td>
                <td class="px-4 py-3 text-center"><span class="px-2 py-0.5 rounded-full text-xs {{ $r->is_hidden ? 'bg-gray-200 text-gray-500' : 'bg-green-100 text-green-700' }}">{{ $r->is_hidden?'숨김':'공개' }}</span></td>
                <td class="px-4 py-3 text-gray-500 text-xs">{{ $r->created_at->format('m-d H:i') }}</td>
                <td class="px-4 py-3 text-right">
                    <form action="{{ route('admin.reviews.toggle-hidden', $r) }}" method="POST" class="inline">@csrf @method('PATCH')
                        <button class="text-xs px-2 py-1 rounded {{ $r->is_hidden?'bg-green-100 text-green-700':'bg-gray-100 text-gray-600' }} hover:bg-gray-200">{{ $r->is_hidden?'공개':'숨김' }}</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="9" class="px-4 py-8 text-center text-gray-400">후기가 없습니다.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $reviews->links() }}</div>
@endsection
