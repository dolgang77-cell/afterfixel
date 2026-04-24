@extends('admin.layouts.app')
@section('title', '미디어 관리')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">미디어 관리 <span class="text-sm font-normal text-red-500">승인대기 {{ $pendingCount }}건</span></h1>
    <a href="{{ route('admin.media.diagnostics') }}" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">점검 화면</a>
</div>

<form method="GET" class="bg-white rounded-xl shadow-sm p-4 mb-6 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs text-gray-500 mb-1">상태</label>
        <select name="status" class="px-3 py-1.5 border rounded-lg text-sm">
            <option value="">전체</option>
            @foreach(['pending' => '승인대기', 'approved' => '승인됨', 'rejected' => '반려', 'hidden' => '숨김'] as $v => $l)
                <option value="{{ $v }}" {{ request('status') === $v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">대상</label>
        <select name="owner_type" class="px-3 py-1.5 border rounded-lg text-sm">
            <option value="">전체</option>
            @foreach(['md_profile' => 'MD', 'club' => '클럽', 'party' => '파티', 'review' => '후기'] as $v => $l)
                <option value="{{ $v }}" {{ request('owner_type') === $v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">업로드 주체</label>
        <select name="uploaded_by_role" class="px-3 py-1.5 border rounded-lg text-sm">
            <option value="">전체</option>
            @foreach(['admin' => '관리자', 'md' => 'MD', 'user' => '일반'] as $v => $l)
                <option value="{{ $v }}" {{ request('uploaded_by_role') === $v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="px-4 py-1.5 bg-gray-800 text-white text-sm rounded-lg">검색</button>
    <a href="{{ route('admin.media.index') }}" class="px-4 py-1.5 bg-gray-200 text-gray-600 text-sm rounded-lg">초기화</a>
</form>

<div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
    @forelse($media as $m)
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <img src="{{ $m->file_url }}" class="w-full h-28 object-cover">
        <div class="p-3 space-y-2">
            @php $sc = ['pending' => 'bg-yellow-100 text-yellow-700', 'approved' => 'bg-green-100 text-green-700', 'rejected' => 'bg-red-100 text-red-700', 'hidden' => 'bg-gray-200 text-gray-500']; @endphp
            <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $sc[$m->approval_status] ?? '' }}">{{ ['pending'=>'승인대기','approved'=>'승인됨','rejected'=>'반려','hidden'=>'숨김'][$m->approval_status] }}</span>
            <p class="text-[10px] text-gray-400">{{ $m->owner_type }} #{{ $m->owner_id }}</p>
            <p class="text-[10px] text-gray-400">by {{ $m->uploader?->name ?? '-' }} ({{ $m->uploaded_by_role }})</p>
            <div class="flex gap-1 flex-wrap">
                @if($m->approval_status !== 'approved')
                <form action="{{ route('admin.media.approve', $m) }}" method="POST" class="inline">@csrf @method('PATCH')
                    <button class="text-[10px] px-2 py-0.5 rounded bg-green-100 text-green-700 hover:bg-green-200">승인</button>
                </form>
                @endif
                @if($m->approval_status !== 'rejected')
                <form action="{{ route('admin.media.reject', $m) }}" method="POST" class="inline">@csrf @method('PATCH')
                    <button class="text-[10px] px-2 py-0.5 rounded bg-red-100 text-red-600 hover:bg-red-200">반려</button>
                </form>
                @endif
                <form action="{{ route('admin.media.destroy', $m) }}" method="POST" class="inline" onsubmit="return confirm('삭제?')">@csrf @method('DELETE')
                    <button class="text-[10px] px-2 py-0.5 rounded bg-gray-100 text-gray-600 hover:bg-gray-200">삭제</button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-full bg-white rounded-xl shadow-sm p-8 text-center text-gray-400">이미지가 없습니다.</div>
    @endforelse
</div>
<div class="mt-4">{{ $media->links() }}</div>
@endsection
