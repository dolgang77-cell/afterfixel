@extends('admin.layouts.app')
@section('title', '회원 상세')

@section('content')
<div class="max-w-4xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.users.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-800">회원 상세</h1>
    </div>

    <div class="grid md:grid-cols-3 gap-6">
        {{-- 기본 정보 --}}
        <div class="md:col-span-2 bg-white rounded-xl shadow-sm p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><p class="text-xs text-gray-400">ID</p><p class="text-gray-700 font-medium">{{ $user->id }}</p></div>
                <div><p class="text-xs text-gray-400">이름</p><p class="text-gray-700 font-medium">{{ $user->name }}</p></div>
                <div><p class="text-xs text-gray-400">이메일</p><p class="text-gray-700">{{ $user->email }}</p></div>
                <div><p class="text-xs text-gray-400">전화번호</p><p class="text-gray-700">{{ $user->phone ?? '-' }}</p></div>
                <div><p class="text-xs text-gray-400">가입일</p><p class="text-gray-700">{{ $user->created_at->format('Y-m-d H:i') }}</p></div>
                <div><p class="text-xs text-gray-400">최근 로그인</p><p class="text-gray-700">{{ $user->last_login_at?->format('Y-m-d H:i') ?? '-' }}</p></div>
            </div>

            <div class="border-t pt-4">
                <h3 class="text-sm font-semibold text-gray-600 mb-3">활동 요약</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><p class="text-xs text-gray-400">찜 개수</p><p class="text-gray-700 font-medium">{{ $favCount }}</p></div>
                    <div><p class="text-xs text-gray-400">최근 본 항목</p><p class="text-gray-700 font-medium">{{ $recentViewCount }}</p></div>
                </div>
            </div>
        </div>

        {{-- 권한 / 상태 변경 --}}
        <div class="space-y-4">
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="text-sm font-semibold text-gray-600 mb-3">권한 변경</h3>
                <form action="{{ route('admin.users.role', $user) }}" method="POST" class="flex gap-2">
                    @csrf @method('PATCH')
                    <select name="role" class="flex-1 px-3 py-1.5 border rounded-lg text-sm">
                        @foreach(['user' => '일반', 'md' => 'MD', 'admin' => '관리자', 'super_admin' => '최고관리자'] as $val => $label)
                            <option value="{{ $val }}" {{ $user->role === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button class="px-3 py-1.5 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700">변경</button>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="text-sm font-semibold text-gray-600 mb-3">상태 변경</h3>
                <form action="{{ route('admin.users.status', $user) }}" method="POST" class="flex gap-2">
                    @csrf @method('PATCH')
                    <select name="status" class="flex-1 px-3 py-1.5 border rounded-lg text-sm">
                        @foreach(['active' => '활성', 'suspended' => '정지', 'withdrawn' => '탈퇴'] as $val => $label)
                            <option value="{{ $val }}" {{ $user->status === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button class="px-3 py-1.5 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700">변경</button>
                </form>
            </div>
        </div>
    </div>

    {{-- 최근 접속 로그 --}}
    @if($recentLogs->isNotEmpty())
    <div class="bg-white rounded-xl shadow-sm p-6 mt-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-600">최근 접속 내역 <span class="text-xs font-normal text-gray-400">(KST)</span></h3>
            <a href="{{ route('admin.access-logs.index', ['user_id' => $user->id]) }}" class="text-xs text-purple-600 hover:underline">전체보기</a>
        </div>
        <table class="w-full text-xs">
            <thead class="text-gray-400 uppercase"><tr>
                <th class="py-2 text-left">시간 (KST)</th><th class="text-left">IP</th><th class="text-left">기기ID</th><th class="text-left">환경</th><th class="text-left">기기/OS</th><th class="text-left">브라우저</th><th class="text-left">경로</th>
            </tr></thead>
            <tbody class="divide-y">
                @foreach($recentLogs as $log)
                <tr class="text-gray-600">
                    <td class="py-2 whitespace-nowrap">{{ $log->created_at_kst_short }}</td>
                    <td class="font-mono">{{ $log->ip_address }}</td>
                    <td class="font-mono max-w-[60px] truncate" title="{{ $log->device_id ?? '-' }}">{{ $log->device_id ? substr($log->device_id, 0, 8) . '...' : '-' }}</td>
                    <td>{{ $log->device_source ?? 'web' }}</td>
                    <td>{{ $log->device_type }} / {{ $log->os }}{{ $log->os_version ? " {$log->os_version}" : '' }}</td>
                    <td>{{ $log->browser }}</td>
                    <td class="max-w-[120px] truncate">{{ $log->path }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
