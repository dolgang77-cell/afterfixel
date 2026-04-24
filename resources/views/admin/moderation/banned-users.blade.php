@extends('admin.layouts.app')
@section('title', '제재 관리')
@section('content')
<h1 class="text-2xl font-bold text-gray-800 mb-6">제재 관리</h1>

{{-- 제재 추가 --}}
<div class="bg-white rounded-xl shadow-sm p-5 mb-6">
    <h3 class="text-sm font-semibold text-gray-600 mb-3">새 제재 추가</h3>
    <form method="POST" action="{{ route('admin.moderation.actions.store') }}" class="flex flex-wrap gap-3 items-end">
        @csrf
        <div><label class="block text-xs text-gray-500 mb-1">회원 ID</label><input type="number" name="user_id" required class="px-3 py-1.5 border rounded-lg text-sm w-20"></div>
        <div><label class="block text-xs text-gray-500 mb-1">유형</label><select name="action_type" required class="px-3 py-1.5 border rounded-lg text-sm">
            <option value="warning">경고</option><option value="restrict_write">글쓰기 제한</option><option value="restrict_upload">업로드 제한</option><option value="suspend">정지</option><option value="ban">영구차단</option>
        </select></div>
        <div><label class="block text-xs text-gray-500 mb-1">사유</label><select name="reason" required class="px-3 py-1.5 border rounded-lg text-sm">
            <option value="abuse">욕설</option><option value="spam">도배</option><option value="ad">광고</option><option value="adult">음란</option><option value="reports">신고누적</option><option value="manual">수동처리</option><option value="other">기타</option>
        </select></div>
        <div><label class="block text-xs text-gray-500 mb-1">기간(일)</label><input type="number" name="duration" min="1" placeholder="비워두면 영구" class="px-3 py-1.5 border rounded-lg text-sm w-20"></div>
        <div><label class="flex items-center gap-1 text-sm"><input type="checkbox" name="is_permanent" value="1" class="rounded border-gray-300 text-purple-600">영구</label></div>
        <div><label class="block text-xs text-gray-500 mb-1">메모</label><input type="text" name="note" class="px-3 py-1.5 border rounded-lg text-sm w-40"></div>
        <button type="submit" class="px-4 py-1.5 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700">제재 적용</button>
    </form>
</div>

{{-- 활성 제재 목록 --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase"><tr>
            <th class="px-4 py-3 text-left">회원</th><th class="text-left">유형</th><th class="text-left">사유</th><th class="text-left">시작</th><th class="text-left">종료</th><th class="text-center">영구</th><th class="text-left">처리자</th><th class="text-right">관리</th>
        </tr></thead>
        <tbody class="divide-y">
            @forelse($actions as $a)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3"><a href="{{ route('admin.users.show', $a->user_id) }}" class="text-purple-600 hover:underline">{{ $a->user?->nickname ?? $a->user_id }}</a></td>
                <td class="px-4 py-3 text-xs">{{ ['warning'=>'경고','restrict_write'=>'글쓰기제한','restrict_upload'=>'업로드제한','suspend'=>'정지','ban'=>'영구차단'][$a->action_type] }}</td>
                <td class="px-4 py-3 text-gray-500 text-xs">{{ $a->reason }}</td>
                <td class="px-4 py-3 text-gray-400 text-xs">{{ $a->starts_at?->format('m-d H:i') }}</td>
                <td class="px-4 py-3 text-gray-400 text-xs">{{ $a->is_permanent ? '영구' : ($a->ends_at?->format('m-d H:i') ?? '-') }}</td>
                <td class="px-4 py-3 text-center">{{ $a->is_permanent ? '✓' : '' }}</td>
                <td class="px-4 py-3 text-gray-500 text-xs">{{ $a->admin?->name ?? '-' }}</td>
                <td class="px-4 py-3 text-right">
                    <form action="{{ route('admin.moderation.actions.deactivate', $a) }}" method="POST" class="inline" onsubmit="return confirm('해제하시겠습니까?')">@csrf @method('PATCH')
                        <button class="text-xs px-2 py-1 rounded bg-green-100 text-green-700 hover:bg-green-200">해제</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="px-4 py-8 text-center text-gray-400">활성 제재가 없습니다.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $actions->links() }}</div>
@endsection
