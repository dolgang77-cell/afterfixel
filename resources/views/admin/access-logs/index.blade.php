@extends('admin.layouts.app')
@section('title', '접속 로그')

@section('content')
<h1 class="text-2xl font-bold text-gray-800 mb-6">접속 로그 <span class="text-sm font-normal text-gray-500">시간 기준: KST (한국시간)</span></h1>

<form method="GET" class="bg-white rounded-xl shadow-sm p-4 mb-6 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs text-gray-500 mb-1">IP</label>
        <input type="text" name="ip" value="{{ request('ip') }}" placeholder="IP 검색" class="px-3 py-1.5 border rounded-lg text-sm w-28">
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">회원 ID</label>
        <input type="number" name="user_id" value="{{ request('user_id') }}" placeholder="ID" class="px-3 py-1.5 border rounded-lg text-sm w-16">
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">기기ID</label>
        <input type="text" name="device_id" value="{{ request('device_id') }}" placeholder="기기ID" class="px-3 py-1.5 border rounded-lg text-sm w-24">
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">guestID</label>
        <input type="text" name="guest_id" value="{{ request('guest_id') }}" placeholder="guestID" class="px-3 py-1.5 border rounded-lg text-sm w-24">
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">기기</label>
        <select name="device" class="px-3 py-1.5 border rounded-lg text-sm">
            <option value="">전체</option>
            @foreach(['mobile' => '모바일', 'desktop' => '데스크탑', 'tablet' => '태블릿'] as $v => $l)
                <option value="{{ $v }}" {{ request('device') === $v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">환경</label>
        <select name="device_source" class="px-3 py-1.5 border rounded-lg text-sm">
            <option value="">전체</option>
            @foreach(['web' => '웹', 'app' => '앱', 'pwa' => 'PWA'] as $v => $l)
                <option value="{{ $v }}" {{ request('device_source') === $v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">로그인</label>
        <select name="login_status" class="px-3 py-1.5 border rounded-lg text-sm">
            <option value="">전체</option>
            <option value="logged_in" {{ request('login_status') === 'logged_in' ? 'selected' : '' }}>회원</option>
            <option value="guest" {{ request('login_status') === 'guest' ? 'selected' : '' }}>비회원</option>
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">시작일 (KST)</label>
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="px-3 py-1.5 border rounded-lg text-sm">
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">종료일 (KST)</label>
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="px-3 py-1.5 border rounded-lg text-sm">
    </div>
    <button type="submit" class="px-4 py-1.5 bg-gray-800 text-white text-sm rounded-lg">검색</button>
    <a href="{{ route('admin.access-logs.index') }}" class="px-4 py-1.5 bg-gray-200 text-gray-600 text-sm rounded-lg">초기화</a>
</form>

<div class="bg-white rounded-xl shadow-sm overflow-x-auto">
    <table class="w-full text-xs">
        <thead class="bg-gray-50 text-gray-500 uppercase">
            <tr>
                <th class="px-3 py-2 text-left">시간 (KST)</th>
                <th class="px-3 py-2 text-left">회원</th>
                <th class="px-3 py-2 text-left">IP</th>
                <th class="px-3 py-2 text-left">기기ID</th>
                <th class="px-3 py-2 text-left">환경</th>
                <th class="px-3 py-2 text-left">기기</th>
                <th class="px-3 py-2 text-left">OS</th>
                <th class="px-3 py-2 text-left">브라우저</th>
                <th class="px-3 py-2 text-left">모델</th>
                <th class="px-3 py-2 text-left">경로</th>
                <th class="px-3 py-2 text-right">상세</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($logs as $log)
            <tr class="hover:bg-gray-50">
                <td class="px-3 py-2 text-gray-600 whitespace-nowrap" title="UTC: {{ $log->created_at->format('Y-m-d H:i:s') }}">{{ $log->created_at_kst_short }}</td>
                <td class="px-3 py-2">
                    @if($log->user_id)
                        <a href="{{ route('admin.users.show', $log->user_id) }}" class="text-purple-600 hover:underline">{{ $log->user_id }}</a>
                    @else
                        <span class="text-gray-400">비회원</span>
                    @endif
                </td>
                <td class="px-3 py-2 text-gray-600 font-mono">{{ $log->ip_address }}</td>
                <td class="px-3 py-2 text-gray-500 font-mono max-w-[80px] truncate" title="{{ $log->device_id ?? $log->guest_id ?? '-' }}">{{ $log->device_id ?? $log->guest_id ?? '-' }}</td>
                <td class="px-3 py-2">
                    @php $srcColor = ['web' => 'bg-gray-100 text-gray-600', 'app' => 'bg-green-100 text-green-700', 'pwa' => 'bg-blue-100 text-blue-700']; @endphp
                    <span class="px-1.5 py-0.5 rounded text-[10px] {{ $srcColor[$log->device_source] ?? 'bg-gray-100 text-gray-600' }}">{{ $log->device_source ?? 'web' }}</span>
                </td>
                <td class="px-3 py-2">
                    <span class="px-1.5 py-0.5 rounded text-[10px] {{ $log->is_mobile ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600' }}">{{ $log->device_type }}</span>
                </td>
                <td class="px-3 py-2 text-gray-600">{{ $log->os }}{{ $log->os_version ? " {$log->os_version}" : '' }}</td>
                <td class="px-3 py-2 text-gray-600">{{ $log->browser }}</td>
                <td class="px-3 py-2 text-gray-500 max-w-[80px] truncate">{{ $log->device_model ?? '-' }}</td>
                <td class="px-3 py-2 text-gray-600 max-w-[120px] truncate">{{ $log->path }}</td>
                <td class="px-3 py-2 text-right">
                    <a href="{{ route('admin.access-logs.show', $log) }}" class="text-purple-600 hover:underline">상세</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="11" class="px-3 py-8 text-center text-gray-400">접속 로그가 없습니다.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $logs->links() }}</div>
@endsection
