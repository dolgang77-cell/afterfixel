@extends('admin.layouts.app')
@section('title', '클럽 관리')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">클럽 관리</h1>
    <a href="{{ route('admin.clubs.create') }}" class="px-4 py-2 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700">+ 클럽 등록</a>
</div>

{{-- 필터 --}}
<form method="GET" class="bg-white rounded-xl shadow-sm p-4 mb-6 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs text-gray-500 mb-1">검색</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="클럽명" class="px-3 py-1.5 border rounded-lg text-sm w-40">
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">지역</label>
        <select name="area" class="px-3 py-1.5 border rounded-lg text-sm">
            <option value="">전체</option>
            @foreach(\App\Models\Club::$areas as $area)
                <option value="{{ $area }}" {{ request('area') === $area ? 'selected' : '' }}>{{ $area }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">장르</label>
        <select name="genre" class="px-3 py-1.5 border rounded-lg text-sm">
            <option value="">전체</option>
            @foreach(\App\Models\Club::$genres as $genre)
                <option value="{{ $genre }}" {{ request('genre') === $genre ? 'selected' : '' }}>{{ $genre }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">상태</label>
        <select name="status" class="px-3 py-1.5 border rounded-lg text-sm">
            <option value="">전체</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>활성</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>비활성</option>
        </select>
    </div>
    <button type="submit" class="px-4 py-1.5 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-700">검색</button>
    <a href="{{ route('admin.clubs.index') }}" class="px-4 py-1.5 bg-gray-200 text-gray-600 text-sm rounded-lg hover:bg-gray-300">초기화</a>
</form>

{{-- 테이블 --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
            <tr>
                <th class="px-4 py-3 text-left">ID</th>
                <th class="px-4 py-3 text-left">클럽명</th>
                <th class="px-4 py-3 text-left">지역</th>
                <th class="px-4 py-3 text-left">장르</th>
                <th class="px-4 py-3 text-center">외국인</th>
                <th class="px-4 py-3 text-center">드레스코드</th>
                <th class="px-4 py-3 text-center">상태</th>
                <th class="px-4 py-3 text-center">조회수</th>
                <th class="px-4 py-3 text-right">관리</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($clubs as $club)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-400">{{ $club->id }}</td>
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $club->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $club->area }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $club->genre }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-block w-5 h-5 {{ $club->foreigner_allowed ? 'text-green-500' : 'text-gray-300' }}">{{ $club->foreigner_allowed ? 'O' : 'X' }}</span>
                    </td>
                    <td class="px-4 py-3 text-center text-gray-600">{{ $club->dress_code ?: '-' }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs {{ $club->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $club->is_active ? '활성' : '비활성' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center text-gray-500">{{ number_format($club->view_count) }}</td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <a href="{{ route('admin.clubs.edit', $club) }}" class="text-blue-600 hover:underline">수정</a>
                        <form action="{{ route('admin.clubs.destroy', $club) }}" method="POST" class="inline" onsubmit="return confirm('정말 삭제하시겠습니까?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-500 hover:underline">삭제</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="px-4 py-8 text-center text-gray-400">등록된 클럽이 없습니다.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $clubs->links() }}</div>
@endsection
