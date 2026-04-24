@extends('admin.layouts.app')
@section('title', '프로필 이미지 검수')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">프로필 이미지 검수</h1>
        <p class="text-sm text-gray-500 mt-1">승인 대기 {{ $pendingCount }}건</p>
    </div>
</div>

<form method="GET" class="bg-white rounded-xl shadow-sm p-4 mb-6 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs text-gray-500 mb-1">상태</label>
        <select name="status" class="px-3 py-1.5 border rounded-lg text-sm">
            @foreach(['pending' => '승인 대기', 'approved' => '승인됨', 'rejected' => '반려됨'] as $value => $label)
                <option value="{{ $value }}" {{ $status === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="px-4 py-1.5 bg-gray-800 text-white text-sm rounded-lg">필터</button>
    <a href="{{ route('admin.profile-images.index') }}" class="px-4 py-1.5 bg-gray-200 text-gray-600 text-sm rounded-lg">초기화</a>
</form>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
    @forelse($images as $image)
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100">
            <img src="{{ $image->image_url }}" alt="profile" class="w-full aspect-square object-cover bg-gray-100">
            <div class="p-4 space-y-3">
                @php
                    $statusClasses = [
                        'pending' => 'bg-yellow-100 text-yellow-700',
                        'approved' => 'bg-green-100 text-green-700',
                        'rejected' => 'bg-red-100 text-red-700',
                    ];
                    $statusLabels = [
                        'pending' => '승인 대기',
                        'approved' => '승인됨',
                        'rejected' => '반려됨',
                    ];
                @endphp
                <div class="flex items-center justify-between gap-3">
                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusClasses[$image->status] ?? 'bg-gray-100 text-gray-600' }}">
                        {{ $statusLabels[$image->status] ?? $image->status }}
                    </span>
                    <span class="text-xs text-gray-400">{{ $image->created_at?->format('Y-m-d H:i') }}</span>
                </div>

                <div class="text-sm text-gray-700">
                    <p class="font-semibold">{{ $image->user?->name ?? '알 수 없음' }}</p>
                    <p class="text-xs text-gray-500">user_id: {{ $image->user_id }} @if($image->user?->nickname) · {{ $image->user->nickname }} @endif</p>
                </div>

                <div class="rounded-xl bg-gray-50 border border-gray-100 p-3 text-xs text-gray-600 space-y-1">
                    <p>검수 공급자: {{ $image->moderation_provider ?: 'conservative' }}</p>
                    <p>판정: {{ $image->moderation_verdict ?: '-' }}</p>
                    <p>점수: {{ $image->moderation_score ?? '-' }}</p>
                    @if($image->rejection_reason)
                        <p>반려 사유: {{ $image->rejection_reason }}</p>
                    @endif
                </div>

                @if($image->status === 'pending')
                    <div class="flex flex-wrap gap-2">
                        <form action="{{ route('admin.profile-images.approve-api') }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="profile_image_id" value="{{ $image->id }}">
                            <button type="submit" class="px-3 py-2 rounded-lg bg-green-600 text-white text-sm hover:bg-green-700">승인</button>
                        </form>

                        <form action="{{ route('admin.profile-images.reject-api') }}" method="POST" class="flex-1 min-w-[220px] flex gap-2">
                            @csrf
                            <input type="hidden" name="profile_image_id" value="{{ $image->id }}">
                            <input type="text" name="reason" maxlength="500" required class="flex-1 px-3 py-2 border rounded-lg text-sm" placeholder="반려 사유 입력">
                            <button type="submit" class="px-3 py-2 rounded-lg bg-red-600 text-white text-sm hover:bg-red-700">반려</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="col-span-full bg-white rounded-2xl shadow-sm p-10 text-center text-gray-400">
            표시할 프로필 이미지가 없습니다.
        </div>
    @endforelse
</div>

<div class="mt-6">
    {{ $images->links() }}
</div>
@endsection
