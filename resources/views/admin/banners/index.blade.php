@extends('admin.layouts.app')
@section('title', '배너/노출 관리')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">배너/노출 관리</h1>
    <a href="{{ route('admin.banners.create') }}" class="px-4 py-2 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700">+ 배너 등록</a>
</div>

@php
    $positionLabels = [
        'home_top'    => '홈 상단',
        'home_middle' => '홈 중간',
        'party_top'   => '파티 상단',
        'club_top'    => '클럽 상단',
    ];
@endphp

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
            <tr>
                <th class="px-4 py-3 text-left">ID</th>
                <th class="px-4 py-3 text-left">제목</th>
                <th class="px-4 py-3 text-left">위치</th>
                <th class="px-4 py-3 text-center">순서</th>
                <th class="px-4 py-3 text-center">상태</th>
                <th class="px-4 py-3 text-left">기간</th>
                <th class="px-4 py-3 text-right">관리</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($banners as $banner)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-400">{{ $banner->id }}</td>
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $banner->title }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $positionLabels[$banner->position] ?? $banner->position }}</td>
                    <td class="px-4 py-3 text-center text-gray-500">{{ $banner->sort_order }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs {{ $banner->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $banner->is_active ? '활성' : '비활성' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">
                        {{ $banner->start_date?->format('Y-m-d') ?? '무제한' }}
                        ~
                        {{ $banner->end_date?->format('Y-m-d') ?? '무제한' }}
                    </td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <a href="{{ route('admin.banners.edit', $banner) }}" class="text-blue-600 hover:underline">수정</a>
                        <form action="{{ route('admin.banners.destroy', $banner) }}" method="POST" class="inline" onsubmit="return confirm('정말 삭제하시겠습니까?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-500 hover:underline">삭제</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">등록된 배너가 없습니다.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $banners->links() }}</div>
@endsection
