@extends('admin.layouts.app')
@section('title', 'MD 관리')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">MD 관리</h1>
    <a href="{{ route('admin.md.create') }}" class="px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700">+ MD 등록</a>
</div>

<form method="GET" class="bg-white rounded-xl shadow-sm p-4 mb-6 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs text-gray-500 mb-1">검색</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="이름" class="px-3 py-1.5 border rounded-lg text-sm w-40">
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">상태</label>
        <select name="status" class="px-3 py-1.5 border rounded-lg text-sm">
            <option value="">전체</option>
            @foreach(['active' => '활동중', 'inactive' => '비활동', 'hidden' => '숨김'] as $val => $label)
                <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="px-4 py-1.5 bg-gray-800 text-white text-sm rounded-lg">검색</button>
</form>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
            <tr>
                <th class="px-4 py-3 text-left">ID</th>
                <th class="px-4 py-3 text-left">이름</th>
                <th class="px-4 py-3 text-left">소속</th>
                <th class="px-4 py-3 text-left">지역</th>
                <th class="px-4 py-3 text-center">상태</th>
                <th class="px-4 py-3 text-center">노출</th>
                <th class="px-4 py-3 text-center">순서</th>
                <th class="px-4 py-3 text-right">관리</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($mds as $md)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-gray-400">{{ $md->id }}</td>
                <td class="px-4 py-3 font-medium text-gray-800">
                    <div class="flex items-center gap-2">
                        @if($md->profile_image)
                            @php $profileSrcset = $md->profile_image_srcset; @endphp
                            <img src="{{ $md->profile_image }}" class="w-8 h-8 rounded-full object-cover" loading="lazy" decoding="async"
                                 @if($profileSrcset) srcset="{{ $profileSrcset }}" sizes="32px" @endif>
                        @else
                            <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 text-xs font-bold">{{ mb_substr($md->display_name, 0, 1) }}</div>
                        @endif
                        {{ $md->display_name }}
                    </div>
                </td>
                <td class="px-4 py-3 text-gray-500 text-xs">{{ $md->affiliation ?? '-' }}</td>
                <td class="px-4 py-3 text-gray-500 text-xs">{{ $md->areas ? implode(', ', $md->areas) : '-' }}</td>
                <td class="px-4 py-3 text-center">
                    @php $sc = ['active' => 'bg-green-100 text-green-700', 'inactive' => 'bg-gray-200 text-gray-500', 'hidden' => 'bg-yellow-100 text-yellow-700']; @endphp
                    <span class="px-2 py-0.5 rounded-full text-xs {{ $sc[$md->status] ?? '' }}">{{ ['active' => '활동중', 'inactive' => '비활동', 'hidden' => '숨김'][$md->status] }}</span>
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="text-xs {{ $md->visible ? 'text-green-600' : 'text-gray-400' }}">{{ $md->visible ? 'ON' : 'OFF' }}</span>
                </td>
                <td class="px-4 py-3 text-center text-gray-500">{{ $md->priority }}</td>
                <td class="px-4 py-3 text-right space-x-1">
                    <a href="{{ route('admin.md.show', $md) }}" class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-600 hover:bg-gray-200">상세</a>
                    <a href="{{ route('admin.md.edit', $md) }}" class="text-xs px-2 py-1 rounded bg-purple-100 text-purple-700 hover:bg-purple-200">수정</a>
                    <form action="{{ route('admin.md.destroy', $md) }}" method="POST" class="inline" onsubmit="return confirm('정말 삭제하시겠습니까?')">
                        @csrf @method('DELETE')
                        <button class="text-xs px-2 py-1 rounded bg-red-100 text-red-600 hover:bg-red-200">삭제</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="px-4 py-8 text-center text-gray-400">등록된 MD가 없습니다.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $mds->links() }}</div>
@endsection
