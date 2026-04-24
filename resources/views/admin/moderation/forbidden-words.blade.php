@extends('admin.layouts.app')
@section('title', '금칙어 관리')
@section('content')
<h1 class="text-2xl font-bold text-gray-800 mb-6">금칙어 관리</h1>

<div class="bg-white rounded-xl shadow-sm p-5 mb-6">
    <h3 class="text-sm font-semibold text-gray-600 mb-3">금칙어 추가</h3>
    <form method="POST" action="{{ route('admin.moderation.forbidden-words.store') }}" class="flex flex-wrap gap-3 items-end">
        @csrf
        <div><label class="block text-xs text-gray-500 mb-1">단어</label><input type="text" name="word" required class="px-3 py-1.5 border rounded-lg text-sm w-40"></div>
        <div><label class="block text-xs text-gray-500 mb-1">매칭</label><select name="match_type" class="px-3 py-1.5 border rounded-lg text-sm"><option value="contains">포함</option><option value="exact">완전일치</option><option value="regex">정규식</option></select></div>
        <div><label class="block text-xs text-gray-500 mb-1">처리</label><select name="action_type" class="px-3 py-1.5 border rounded-lg text-sm"><option value="block">차단</option><option value="mask">마스킹</option><option value="review">검토대기</option><option value="warn">경고</option></select></div>
        <div><label class="block text-xs text-gray-500 mb-1">카테고리</label><select name="category" class="px-3 py-1.5 border rounded-lg text-sm"><option value="abuse">욕설</option><option value="spam">스팸</option><option value="adult">음란</option><option value="discrimination">차별</option><option value="general">일반</option></select></div>
        <div><label class="block text-xs text-gray-500 mb-1">심각도</label><input type="number" name="severity" value="1" min="1" max="5" class="px-3 py-1.5 border rounded-lg text-sm w-16"></div>
        <button type="submit" class="px-4 py-1.5 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700">추가</button>
    </form>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase"><tr>
            <th class="px-4 py-3 text-left">단어</th><th class="text-left">매칭</th><th class="text-left">처리</th><th class="text-left">카테고리</th><th class="text-center">심각도</th><th class="text-center">상태</th><th class="text-right">관리</th>
        </tr></thead>
        <tbody class="divide-y">
            @forelse($words as $w)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-medium text-gray-800 font-mono">{{ $w->word }}</td>
                <td class="px-4 py-3 text-gray-500 text-xs">{{ ['contains'=>'포함','exact'=>'완전일치','regex'=>'정규식'][$w->match_type] }}</td>
                <td class="px-4 py-3 text-xs">
                    @php $ac=['block'=>'bg-red-100 text-red-700','mask'=>'bg-yellow-100 text-yellow-700','review'=>'bg-blue-100 text-blue-700','warn'=>'bg-gray-100 text-gray-600']; @endphp
                    <span class="px-2 py-0.5 rounded-full {{ $ac[$w->action_type]??'' }}">{{ ['block'=>'차단','mask'=>'마스킹','review'=>'검토','warn'=>'경고'][$w->action_type] }}</span>
                </td>
                <td class="px-4 py-3 text-gray-500 text-xs">{{ $w->category }}</td>
                <td class="px-4 py-3 text-center text-gray-500">{{ $w->severity }}</td>
                <td class="px-4 py-3 text-center"><span class="{{ $w->is_active ? 'text-green-600' : 'text-gray-400' }}">{{ $w->is_active ? 'ON' : 'OFF' }}</span></td>
                <td class="px-4 py-3 text-right space-x-1">
                    <form action="{{ route('admin.moderation.forbidden-words.toggle', $w) }}" method="POST" class="inline">@csrf @method('PATCH')<button class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-600 hover:bg-gray-200">{{ $w->is_active ? 'OFF' : 'ON' }}</button></form>
                    <form action="{{ route('admin.moderation.forbidden-words.destroy', $w) }}" method="POST" class="inline" onsubmit="return confirm('삭제?')">@csrf @method('DELETE')<button class="text-xs px-2 py-1 rounded bg-red-100 text-red-600 hover:bg-red-200">삭제</button></form>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">등록된 금칙어가 없습니다.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $words->links() }}</div>
@endsection
