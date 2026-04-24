@extends('admin.layouts.app')
@section('title', '게시글 관리')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">게시글 관리</h1>
    <a href="{{ route('admin.posts.create') }}" class="px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700">+ 게시글 등록</a>
</div>

{{-- 필터 --}}
<form method="GET" class="bg-white rounded-xl shadow-sm p-4 mb-6 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs text-gray-500 mb-1">검색</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="제목/내용/작성자" class="px-3 py-1.5 border rounded-lg text-sm w-44">
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">필터</label>
        <select name="filter" class="px-3 py-1.5 border rounded-lg text-sm">
            <option value="">전체</option>
            <option value="reported" {{ request('filter') === 'reported' ? 'selected' : '' }}>신고됨</option>
            <option value="hidden" {{ request('filter') === 'hidden' ? 'selected' : '' }}>숨김</option>
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">유형</label>
        <select name="type" class="px-3 py-1.5 border rounded-lg text-sm">
            <option value="">전체</option>
            @foreach(\App\Models\CommunityPost::$types as $key => $label)
                <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="px-4 py-1.5 bg-gray-800 text-white text-sm rounded-lg">검색</button>
    <a href="{{ route('admin.posts.index') }}" class="px-4 py-1.5 bg-gray-200 text-gray-600 text-sm rounded-lg">초기화</a>
</form>

{{-- 일괄 숨김 폼 --}}
<form method="POST" action="{{ route('admin.posts.bulk-hide') }}" id="bulkForm" x-data="bulkSelect()">
    @csrf

    {{-- 일괄 액션 바 --}}
    <div x-show="selected.length > 0" x-cloak
         class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg flex items-center gap-3 text-sm">
        <span class="text-yellow-700 font-medium" x-text="selected.length + '개 선택됨'"></span>
        <button type="submit" class="px-3 py-1 bg-yellow-500 text-white rounded-lg text-xs font-medium hover:bg-yellow-600">선택 일괄 숨김</button>
    </div>

    {{-- 테이블 --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                <tr>
                    <th class="px-3 py-3 text-center w-10">
                        <input type="checkbox" @click="toggleAll($event)" class="rounded border-gray-300">
                    </th>
                    <th class="px-4 py-3 text-left">ID</th>
                    <th class="px-4 py-3 text-left">제목</th>
                    <th class="px-4 py-3 text-left">작성자</th>
                    <th class="px-4 py-3 text-left">유형</th>
                    <th class="px-4 py-3 text-left">클럽</th>
                    <th class="px-4 py-3 text-center">신고</th>
                    <th class="px-4 py-3 text-center">상태</th>
                    <th class="px-4 py-3 text-left">작성일</th>
                    <th class="px-4 py-3 text-right">관리</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($posts as $post)
                    <tr class="hover:bg-gray-50 {{ $post->report_count >= 3 ? 'bg-red-50' : '' }}">
                        <td class="px-3 py-3 text-center">
                            <input type="checkbox" name="ids[]" value="{{ $post->id }}"
                                   @click="toggle({{ $post->id }})" :checked="selected.includes({{ $post->id }})"
                                   class="rounded border-gray-300">
                        </td>
                        <td class="px-4 py-3 text-gray-400">{{ $post->id }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.posts.show', $post) }}" class="font-medium text-gray-800 hover:text-purple-600 max-w-[200px] truncate block">{{ $post->title }}</a>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $post->nickname }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs rounded-full">{{ $post->type_text }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-xs">{{ $post->club?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($post->report_count > 0)
                                <span class="px-2 py-0.5 bg-red-100 text-red-600 text-xs rounded-full font-medium">{{ $post->report_count }}</span>
                            @else
                                <span class="text-gray-300">0</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs {{ $post->is_hidden ? 'bg-gray-200 text-gray-500' : 'bg-green-100 text-green-700' }}">
                                {{ $post->is_hidden ? '숨김' : '공개' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $post->created_at->format('m-d H:i') }}</td>
                        <td class="px-4 py-3 text-right space-x-1">
                            <a href="{{ route('admin.posts.show', $post) }}" class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-600 hover:bg-gray-200">상세</a>
                            <a href="{{ route('admin.posts.edit', $post) }}" class="text-xs px-2 py-1 rounded bg-purple-100 text-purple-700 hover:bg-purple-200">수정</a>
                            <form action="{{ route('admin.posts.toggle-hidden', $post) }}" method="POST" class="inline">
                                @csrf @method('PATCH')
                                <button type="submit" class="text-xs px-2 py-1 rounded {{ $post->is_hidden ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                    {{ $post->is_hidden ? '공개' : '숨김' }}
                                </button>
                            </form>
                            @if($post->report_count > 0)
                                <form action="{{ route('admin.posts.reset-reports', $post) }}" method="POST" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="text-xs px-2 py-1 rounded bg-yellow-100 text-yellow-700 hover:bg-yellow-200">초기화</button>
                                </form>
                            @endif
                            <form action="{{ route('admin.posts.destroy', $post) }}" method="POST" class="inline" onsubmit="return confirm('정말 삭제하시겠습니까?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs px-2 py-1 rounded bg-red-100 text-red-600 hover:bg-red-200">삭제</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="px-4 py-8 text-center text-gray-400">게시글이 없습니다.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</form>

<div class="mt-4">{{ $posts->links() }}</div>

<script>
function bulkSelect() {
    return {
        selected: [],
        toggle(id) {
            const idx = this.selected.indexOf(id);
            idx === -1 ? this.selected.push(id) : this.selected.splice(idx, 1);
        },
        toggleAll(e) {
            if (e.target.checked) {
                this.selected = @json($posts->pluck('id'));
            } else {
                this.selected = [];
            }
        },
    };
}
</script>
@endsection
