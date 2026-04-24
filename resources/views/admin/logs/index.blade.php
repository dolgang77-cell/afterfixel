@extends('admin.layouts.app')
@section('title', '관리 로그')

@section('content')
<h1 class="text-2xl font-bold text-gray-800 mb-6">관리 로그</h1>

<form method="GET" class="bg-white rounded-xl shadow-sm p-4 mb-6 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs text-gray-500 mb-1">액션</label>
        <select name="action" class="px-3 py-1.5 border rounded-lg text-sm">
            <option value="">전체</option>
            @foreach(['create', 'update', 'delete', 'hide', 'unhide', 'reset_reports', 'update_priority'] as $action)
                <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ $action }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">대상</label>
        <select name="target_type" class="px-3 py-1.5 border rounded-lg text-sm">
            <option value="">전체</option>
            @foreach(['club', 'party', 'community_post', 'banner'] as $type)
                <option value="{{ $type }}" {{ request('target_type') === $type ? 'selected' : '' }}>{{ $type }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="px-4 py-1.5 bg-gray-800 text-white text-sm rounded-lg">검색</button>
    <a href="{{ route('admin.logs.index') }}" class="px-4 py-1.5 bg-gray-200 text-gray-600 text-sm rounded-lg">초기화</a>
</form>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
            <tr>
                <th class="px-4 py-3 text-left">시간</th>
                <th class="px-4 py-3 text-left">관리자</th>
                <th class="px-4 py-3 text-left">액션</th>
                <th class="px-4 py-3 text-left">대상</th>
                <th class="px-4 py-3 text-left">상세</th>
                <th class="px-4 py-3 text-left">IP</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($logs as $log)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ $log->user?->name ?? '-' }}</td>
                    <td class="px-4 py-3">
                        @php
                            $actionColors = [
                                'create' => 'bg-green-100 text-green-700',
                                'update' => 'bg-blue-100 text-blue-700',
                                'delete' => 'bg-red-100 text-red-600',
                                'hide'   => 'bg-yellow-100 text-yellow-700',
                                'unhide' => 'bg-purple-100 text-purple-700',
                            ];
                        @endphp
                        <span class="px-2 py-0.5 rounded-full text-xs {{ $actionColors[$log->action] ?? 'bg-gray-100 text-gray-600' }}">{{ $log->action }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $log->target_type }}#{{ $log->target_id }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs max-w-xs truncate">{{ $log->details ? json_encode($log->details, JSON_UNESCAPED_UNICODE) : '-' }}</td>
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $log->ip_address }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">관리 로그가 없습니다.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $logs->links() }}</div>
@endsection
