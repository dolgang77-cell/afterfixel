@extends('admin.layouts.app')
@section('title', '게시글 상세')

@section('content')
<div class="max-w-3xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.posts.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-800">게시글 상세</h1>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        {{-- 상태 뱃지 --}}
        <div class="flex items-center gap-2 mb-4">
            <span class="px-2 py-0.5 rounded-full text-xs {{ $post->is_hidden ? 'bg-gray-200 text-gray-500' : 'bg-green-100 text-green-700' }}">
                {{ $post->is_hidden ? '숨김' : '공개' }}
            </span>
            <span class="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs rounded-full">{{ $post->type_text }}</span>
            @if($post->report_count > 0)
                <span class="px-2 py-0.5 bg-red-100 text-red-600 text-xs rounded-full font-medium">신고 {{ $post->report_count }}회</span>
            @endif
        </div>

        {{-- 메타 정보 --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 text-sm">
            <div>
                <p class="text-xs text-gray-400">작성자</p>
                <p class="text-gray-700 font-medium">{{ $post->nickname }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">클럽</p>
                <p class="text-gray-700">{{ $post->club?->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">작성일</p>
                <p class="text-gray-700">{{ $post->created_at->format('Y-m-d H:i') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">좋아요</p>
                <p class="text-gray-700">{{ $post->like_count }}</p>
            </div>
        </div>

        {{-- 제목 --}}
        <h2 class="text-lg font-bold text-gray-800 mb-3">{{ $post->title }}</h2>

        {{-- 본문 --}}
        <div class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap bg-gray-50 rounded-lg p-4 mb-4">{{ $post->content }}</div>

        {{-- 이미지 --}}
        @if($post->images && count($post->images) > 0)
            <div class="flex gap-2 flex-wrap mb-4">
                @foreach($post->images as $img)
                    <img src="{{ $img }}" alt="" class="w-32 h-32 object-cover rounded-lg border">
                @endforeach
            </div>
        @endif

        {{-- 세션 정보 --}}
        <div class="text-xs text-gray-400 border-t pt-3">
            Session: {{ $post->session_id }} &nbsp;|&nbsp; ID: {{ $post->id }}
        </div>
    </div>

    {{-- 관리 액션 --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-800 mb-4">관리 액션</h3>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.posts.edit', $post) }}" class="px-4 py-2 rounded-lg text-sm font-medium bg-purple-100 text-purple-700 hover:bg-purple-200">수정</a>

            <form action="{{ route('admin.posts.toggle-hidden', $post) }}" method="POST">
                @csrf @method('PATCH')
                <button type="submit" class="px-4 py-2 rounded-lg text-sm font-medium {{ $post->is_hidden ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200' }}">
                    {{ $post->is_hidden ? '공개로 전환' : '숨김 처리' }}
                </button>
            </form>

            @if($post->report_count > 0)
                <form action="{{ route('admin.posts.reset-reports', $post) }}" method="POST">
                    @csrf @method('PATCH')
                    <button type="submit" class="px-4 py-2 rounded-lg text-sm font-medium bg-blue-100 text-blue-700 hover:bg-blue-200">
                        신고 초기화 ({{ $post->report_count }}건)
                    </button>
                </form>
            @endif

            <form action="{{ route('admin.posts.destroy', $post) }}" method="POST" onsubmit="return confirm('정말 삭제하시겠습니까? 이 작업은 되돌릴 수 없습니다.')">
                @csrf @method('DELETE')
                <button type="submit" class="px-4 py-2 rounded-lg text-sm font-medium bg-red-100 text-red-600 hover:bg-red-200">
                    영구 삭제
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
