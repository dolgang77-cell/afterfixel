@extends('admin.layouts.app')
@section('title', '회원 관리')

@section('content')
<h1 class="text-2xl font-bold text-gray-800 mb-6">회원 관리</h1>

<form method="GET" class="bg-white rounded-xl shadow-sm p-4 mb-6 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs text-gray-500 mb-1">검색</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="이름/이메일/전화" class="px-3 py-1.5 border rounded-lg text-sm w-48">
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">권한</label>
        <select name="role" class="px-3 py-1.5 border rounded-lg text-sm">
            <option value="">전체</option>
            @foreach(['user' => '일반', 'md' => 'MD', 'admin' => '관리자', 'super_admin' => '최고관리자'] as $val => $label)
                <option value="{{ $val }}" {{ request('role') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">상태</label>
        <select name="status" class="px-3 py-1.5 border rounded-lg text-sm">
            <option value="">전체</option>
            @foreach(['active' => '활성', 'suspended' => '정지', 'withdrawn' => '탈퇴'] as $val => $label)
                <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="px-4 py-1.5 bg-gray-800 text-white text-sm rounded-lg">검색</button>
    <a href="{{ route('admin.users.index') }}" class="px-4 py-1.5 bg-gray-200 text-gray-600 text-sm rounded-lg">초기화</a>
</form>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
            <tr>
                <th class="px-4 py-3 text-left">ID</th>
                <th class="px-4 py-3 text-left">이름</th>
                <th class="px-4 py-3 text-left">이메일</th>
                <th class="px-4 py-3 text-left">전화</th>
                <th class="px-4 py-3 text-center">권한</th>
                <th class="px-4 py-3 text-center">상태</th>
                <th class="px-4 py-3 text-left">최근 로그인</th>
                <th class="px-4 py-3 text-left">가입일</th>
                <th class="px-4 py-3 text-right">관리</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($users as $user)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-gray-400">{{ $user->id }}</td>
                <td class="px-4 py-3 font-medium text-gray-800">{{ $user->name }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $user->email }}</td>
                <td class="px-4 py-3 text-gray-500 text-xs">{{ $user->phone ?? '-' }}</td>
                <td class="px-4 py-3 text-center">
                    @php $roleColors = ['user' => 'bg-gray-100 text-gray-600', 'md' => 'bg-blue-100 text-blue-700', 'admin' => 'bg-purple-100 text-purple-700', 'super_admin' => 'bg-red-100 text-red-700']; @endphp
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $roleColors[$user->role] ?? '' }}">{{ $user->role }}</span>
                </td>
                <td class="px-4 py-3 text-center">
                    @php $statusColors = ['active' => 'bg-green-100 text-green-700', 'suspended' => 'bg-yellow-100 text-yellow-700', 'withdrawn' => 'bg-gray-200 text-gray-500']; @endphp
                    <span class="px-2 py-0.5 rounded-full text-xs {{ $statusColors[$user->status] ?? '' }}">{{ ['active' => '활성', 'suspended' => '정지', 'withdrawn' => '탈퇴'][$user->status] ?? $user->status }}</span>
                </td>
                <td class="px-4 py-3 text-gray-500 text-xs">{{ $user->last_login_at?->format('m-d H:i') ?? '-' }}</td>
                <td class="px-4 py-3 text-gray-500 text-xs">{{ $user->created_at->format('Y-m-d') }}</td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('admin.users.show', $user) }}" class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-600 hover:bg-gray-200">상세</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="9" class="px-4 py-8 text-center text-gray-400">회원이 없습니다.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $users->links() }}</div>
@endsection
