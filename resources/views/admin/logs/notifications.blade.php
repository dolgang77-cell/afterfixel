@extends('admin.layouts.app')
@section('title', '알림 로그')

@section('content')
<h1 class="text-2xl font-bold text-gray-800 mb-6">알림 발송 로그</h1>

{{-- 통계 --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    @foreach($typeCounts as $type => $count)
        @php
            $typeLabels = [
                'party_reminder'          => '파티 리마인더',
                'new_party_match'         => '새 파티 매칭',
                'tonight_recommendation'  => '오늘밤 추천',
            ];
            $typeColors = [
                'party_reminder'          => 'border-blue-500',
                'new_party_match'         => 'border-green-500',
                'tonight_recommendation'  => 'border-purple-500',
            ];
        @endphp
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 {{ $typeColors[$type] ?? 'border-gray-500' }}">
            <p class="text-xs text-gray-500">{{ $typeLabels[$type] ?? $type }}</p>
            <p class="text-xl font-bold text-gray-800 mt-1">{{ number_format($count) }}</p>
        </div>
    @endforeach
    <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-yellow-500">
        <p class="text-xs text-gray-500">읽음 비율</p>
        <p class="text-xl font-bold text-gray-800 mt-1">{{ $readRate }}%</p>
    </div>
</div>

{{-- 필터 --}}
<form method="GET" class="bg-white rounded-xl shadow-sm p-4 mb-6 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs text-gray-500 mb-1">유형</label>
        <select name="type" class="px-3 py-1.5 border rounded-lg text-sm">
            <option value="">전체</option>
            <option value="party_reminder" {{ request('type') === 'party_reminder' ? 'selected' : '' }}>파티 리마인더</option>
            <option value="new_party_match" {{ request('type') === 'new_party_match' ? 'selected' : '' }}>새 파티 매칭</option>
            <option value="tonight_recommendation" {{ request('type') === 'tonight_recommendation' ? 'selected' : '' }}>오늘밤 추천</option>
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">채널</label>
        <select name="channel" class="px-3 py-1.5 border rounded-lg text-sm">
            <option value="">전체</option>
            <option value="in_app" {{ request('channel') === 'in_app' ? 'selected' : '' }}>인앱</option>
            <option value="web_push" {{ request('channel') === 'web_push' ? 'selected' : '' }}>웹 푸시</option>
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">읽음 여부</label>
        <select name="read" class="px-3 py-1.5 border rounded-lg text-sm">
            <option value="">전체</option>
            <option value="read" {{ request('read') === 'read' ? 'selected' : '' }}>읽음</option>
            <option value="unread" {{ request('read') === 'unread' ? 'selected' : '' }}>안 읽음</option>
        </select>
    </div>
    <button type="submit" class="px-4 py-1.5 bg-gray-800 text-white text-sm rounded-lg">검색</button>
    <a href="{{ route('admin.logs.notifications') }}" class="px-4 py-1.5 bg-gray-200 text-gray-600 text-sm rounded-lg">초기화</a>
</form>

{{-- 테이블 --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
            <tr>
                <th class="px-4 py-3 text-left">시간</th>
                <th class="px-4 py-3 text-left">유형</th>
                <th class="px-4 py-3 text-left">제목</th>
                <th class="px-4 py-3 text-left">내용</th>
                <th class="px-4 py-3 text-center">채널</th>
                <th class="px-4 py-3 text-center">읽음</th>
                <th class="px-4 py-3 text-left">세션</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($notifications as $notif)
                @php
                    $typeColors = [
                        'party_reminder'          => 'bg-blue-100 text-blue-700',
                        'new_party_match'         => 'bg-green-100 text-green-700',
                        'tonight_recommendation'  => 'bg-purple-100 text-purple-700',
                    ];
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">{{ $notif->created_at->format('m-d H:i') }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs {{ $typeColors[$notif->type] ?? 'bg-gray-100 text-gray-600' }}">{{ $notif->type }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-800 max-w-[200px] truncate">{{ $notif->title }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs max-w-[250px] truncate">{{ $notif->body }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-xs {{ $notif->channel === 'web_push' ? 'text-orange-600' : 'text-gray-500' }}">{{ $notif->channel }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($notif->is_read)
                            <span class="text-green-500 text-xs">읽음</span>
                        @else
                            <span class="text-gray-300 text-xs">미읽음</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ substr($notif->session_id, 0, 8) }}...</td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">알림 로그가 없습니다.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $notifications->links() }}</div>
@endsection
