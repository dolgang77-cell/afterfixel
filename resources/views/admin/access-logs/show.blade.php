@extends('admin.layouts.app')
@section('title', '접속 로그 상세')

@section('content')
<div class="max-w-3xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.access-logs.index') }}" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg></a>
        <h1 class="text-2xl font-bold text-gray-800">접속 로그 상세 #{{ $log->id }}</h1>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 space-y-6">
        {{-- 시간 정보 --}}
        <div>
            <h3 class="text-sm font-semibold text-gray-600 mb-3 border-b pb-2">시간 정보</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><p class="text-xs text-gray-400">접속 시간 (KST)</p><p class="text-gray-800 font-medium">{{ $log->created_at_kst }}</p></div>
                <div><p class="text-xs text-gray-400">접속 시간 (UTC)</p><p class="text-gray-600">{{ $log->created_at->format('Y-m-d H:i:s') }}</p></div>
                <div><p class="text-xs text-gray-400">클라이언트 시간대</p><p class="text-gray-600">{{ $log->client_timezone ?? '-' }}</p></div>
                <div><p class="text-xs text-gray-400">시간대 오프셋 (분)</p><p class="text-gray-600">{{ $log->client_timezone_offset !== null ? $log->client_timezone_offset : '-' }}</p></div>
            </div>
        </div>

        {{-- 사용자 정보 --}}
        <div>
            <h3 class="text-sm font-semibold text-gray-600 mb-3 border-b pb-2">사용자 정보</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><p class="text-xs text-gray-400">회원 ID</p><p>@if($log->user_id)<a href="{{ route('admin.users.show', $log->user_id) }}" class="text-purple-600 hover:underline">{{ $log->user_id }} ({{ $log->user?->name }})</a>@else<span class="text-gray-400">비회원</span>@endif</p></div>
                <div><p class="text-xs text-gray-400">로그인 상태</p><p class="{{ $log->is_logged_in ? 'text-green-600' : 'text-gray-400' }}">{{ $log->is_logged_in ? '로그인' : '비로그인' }}</p></div>
                <div><p class="text-xs text-gray-400">Session ID</p><p class="text-gray-600 font-mono text-xs break-all">{{ $log->session_id ?? '-' }}</p></div>
                <div><p class="text-xs text-gray-400">Guest ID</p><p class="text-gray-600 font-mono text-xs">{{ $log->guest_id ?? '-' }}</p></div>
            </div>
        </div>

        {{-- 기기 정보 --}}
        <div>
            <h3 class="text-sm font-semibold text-gray-600 mb-3 border-b pb-2">기기 정보</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><p class="text-xs text-gray-400">기기식별자 (Device ID)</p><p class="text-gray-800 font-mono text-xs">{{ $log->device_id ?? '-' }}</p></div>
                <div><p class="text-xs text-gray-400">접속 환경</p>
                    @php $srcColor = ['web' => 'bg-gray-100 text-gray-600', 'app' => 'bg-green-100 text-green-700', 'pwa' => 'bg-blue-100 text-blue-700']; @endphp
                    <span class="px-2 py-0.5 rounded text-xs {{ $srcColor[$log->device_source] ?? 'bg-gray-100' }}">{{ $log->device_source ?? 'web' }}</span>
                </div>
                <div><p class="text-xs text-gray-400">기기 유형</p><p class="text-gray-600">{{ $log->device_type }}</p></div>
                <div><p class="text-xs text-gray-400">모바일 여부</p><p class="{{ $log->is_mobile ? 'text-blue-600' : 'text-gray-400' }}">{{ $log->is_mobile ? '모바일' : 'PC' }}</p></div>
                <div><p class="text-xs text-gray-400">기기 모델</p><p class="text-gray-600">{{ $log->device_model ?? '-' }}</p></div>
                <div><p class="text-xs text-gray-400">언어</p><p class="text-gray-600">{{ $log->language ?? '-' }}</p></div>
                <div><p class="text-xs text-gray-400">OS</p><p class="text-gray-600">{{ $log->os }}{{ $log->os_version ? " {$log->os_version}" : '' }}</p></div>
                <div><p class="text-xs text-gray-400">브라우저</p><p class="text-gray-600">{{ $log->browser }}</p></div>
                <div><p class="text-xs text-gray-400">앱 버전</p><p class="text-gray-600">{{ $log->app_version ?? '-' }}</p></div>
                <div><p class="text-xs text-gray-400">빌드 버전</p><p class="text-gray-600">{{ $log->build_version ?? '-' }}</p></div>
            </div>
        </div>

        {{-- 네트워크/경로 --}}
        <div>
            <h3 class="text-sm font-semibold text-gray-600 mb-3 border-b pb-2">네트워크 / 경로</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><p class="text-xs text-gray-400">IP 주소</p><p class="text-gray-800 font-mono">{{ $log->ip_address }}</p></div>
                <div><p class="text-xs text-gray-400">접속 경로</p><p class="text-gray-600">{{ $log->path }}</p></div>
                <div class="col-span-2"><p class="text-xs text-gray-400">레퍼러</p><p class="text-gray-600 break-all">{{ $log->referer ?? '-' }}</p></div>
            </div>
        </div>

        {{-- User Agent 원본 --}}
        <div>
            <h3 class="text-sm font-semibold text-gray-600 mb-3 border-b pb-2">User-Agent 원본</h3>
            <p class="text-xs text-gray-500 bg-gray-50 rounded-lg p-3 break-all font-mono leading-relaxed">{{ $log->user_agent ?? '-' }}</p>
        </div>
    </div>
</div>
@endsection
