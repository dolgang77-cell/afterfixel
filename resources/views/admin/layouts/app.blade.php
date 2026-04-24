<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', '관리자') - VYBE Admin</title>
    <script src="/vendor/tailwindcss-3.4.17.js"></script>
    <script defer src="/vendor/alpinejs-3.15.11.min.js"></script>
    <script>
        (() => {
            const fallbackSrc = @js(url('/images/placeholders/image-fallback.svg'));

            document.addEventListener('error', (event) => {
                const el = event.target;

                if (!(el instanceof HTMLImageElement)) {
                    return;
                }

                const nextSrc = el.dataset.fallbackSrc || fallbackSrc;

                if (!nextSrc || el.dataset.fallbackApplied === '1' || el.currentSrc === nextSrc || el.src === nextSrc) {
                    return;
                }

                el.dataset.fallbackApplied = '1';
                el.src = nextSrc;
            }, true);
        })();
    </script>
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: true }" data-page-type="@yield('page_type', 'admin')">
    {{-- 사이드바 --}}
    <aside class="fixed inset-y-0 left-0 z-30 w-60 bg-gray-900 text-white transform transition-transform duration-200"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
        <div class="flex items-center justify-between h-14 px-4 bg-gray-800">
            <a href="{{ route('admin.dashboard') }}" class="text-lg font-bold tracking-wide">VYBE Admin</a>
            <button @click="sidebarOpen = false" class="lg:hidden text-gray-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <nav class="mt-4 space-y-1 px-3 overflow-y-auto" style="max-height: calc(100vh - 56px)">
            @php $current = request()->route()->getName(); @endphp

            <p class="px-3 pt-2 pb-1 text-[10px] font-semibold text-gray-500 uppercase tracking-wider">콘텐츠</p>

            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ str_starts_with($current, 'admin.dashboard') ? 'bg-purple-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                <span>📊</span> 대시보드
            </a>
            <a href="{{ route('admin.clubs.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ str_starts_with($current, 'admin.clubs') ? 'bg-purple-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                <span>🏢</span> 클럽 관리
            </a>
            <a href="{{ route('admin.parties.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ str_starts_with($current, 'admin.parties') ? 'bg-purple-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                <span>🎉</span> 파티 관리
            </a>
            <a href="{{ route('admin.posts.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ str_starts_with($current, 'admin.posts') ? 'bg-purple-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                <span>📝</span> 게시글 관리
                @php $reportedCount = \App\Models\CommunityPost::where('report_count', '>', 0)->where('is_hidden', false)->count(); @endphp
                @if($reportedCount > 0)
                    <span class="ml-auto bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $reportedCount }}</span>
                @endif
            </a>

            <p class="px-3 pt-4 pb-1 text-[10px] font-semibold text-gray-500 uppercase tracking-wider">회원</p>

            <a href="{{ route('admin.users.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ str_starts_with($current, 'admin.users') ? 'bg-purple-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                <span>👤</span> 회원 관리
            </a>
            <a href="{{ route('admin.md.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ str_starts_with($current, 'admin.md') && !str_starts_with($current, 'admin.md-mappings') ? 'bg-purple-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                <span>🎤</span> MD 관리
            </a>
            <a href="{{ route('admin.md-mappings.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ str_starts_with($current, 'admin.md-mappings') ? 'bg-purple-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                <span>🔗</span> MD 매칭
            </a>

            <p class="px-3 pt-4 pb-1 text-[10px] font-semibold text-gray-500 uppercase tracking-wider">운영</p>

            <a href="{{ route('admin.exposure.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ str_starts_with($current, 'admin.exposure') ? 'bg-purple-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                <span>🎯</span> 노출 관리
            </a>
            <a href="{{ route('admin.banners.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ str_starts_with($current, 'admin.banners') ? 'bg-purple-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                <span>🖼️</span> 배너 관리
            </a>
            <a href="{{ route('admin.media.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ $current === 'admin.media.index' ? 'bg-purple-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                <span>📷</span> 미디어 관리
                @php $pendingMedia = \App\Models\Media::pending()->count(); @endphp
                @if($pendingMedia > 0)<span class="ml-auto bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $pendingMedia }}</span>@endif
            </a>
            <a href="{{ route('admin.media.diagnostics') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ $current === 'admin.media.diagnostics' ? 'bg-purple-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                <span>🩺</span> 미디어 점검
            </a>
            <a href="{{ route('admin.profile-images.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ str_starts_with($current, 'admin.profile-images') ? 'bg-purple-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                <span>🧑</span> 프로필 이미지
                @php
                    $pendingProfileImages = \Illuminate\Support\Facades\Schema::hasTable('profile_images')
                        ? \App\Models\ProfileImage::pending()->count()
                        : 0;
                @endphp
                @if($pendingProfileImages > 0)<span class="ml-auto bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $pendingProfileImages }}</span>@endif
            </a>
            <a href="{{ route('admin.reviews.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ str_starts_with($current, 'admin.reviews') ? 'bg-purple-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                <span>⭐</span> 후기 관리
            </a>
            <a href="{{ route('admin.inquiries.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ str_starts_with($current, 'admin.inquiries') ? 'bg-purple-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                <span>📨</span> 문의 관리
                @php $pendingInq = \App\Models\Inquiry::pending()->count(); @endphp
                @if($pendingInq > 0)<span class="ml-auto bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $pendingInq }}</span>@endif
            </a>

            <p class="px-3 pt-4 pb-1 text-[10px] font-semibold text-gray-500 uppercase tracking-wider">마케팅</p>

            <a href="{{ route('admin.push.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ str_starts_with($current, 'admin.push') ? 'bg-purple-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                <span>📢</span> 푸쉬 관리
            </a>

            <p class="px-3 pt-4 pb-1 text-[10px] font-semibold text-gray-500 uppercase tracking-wider">운영정책</p>

            <a href="{{ route('admin.moderation.reports') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ $current === 'admin.moderation.reports' ? 'bg-purple-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                <span>🚨</span> 신고 관리
                @php $reportPending = \App\Models\Report::pending()->count(); @endphp
                @if($reportPending > 0)<span class="ml-auto bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $reportPending }}</span>@endif
            </a>
            @if(config('nearby-messaging.enabled') && \Illuminate\Support\Facades\Schema::hasTable('message_reports'))
                <a href="{{ route('admin.moderation.message-reports') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ $current === 'admin.moderation.message-reports' ? 'bg-purple-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <span>💬</span> 메시지 신고
                    @php $messageReportPending = \App\Models\MessageReport::pending()->count(); @endphp
                    @if($messageReportPending > 0)<span class="ml-auto bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $messageReportPending }}</span>@endif
                </a>
            @endif
            <a href="{{ route('admin.moderation.banned-users') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ $current === 'admin.moderation.banned-users' ? 'bg-purple-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                <span>🚫</span> 제재 관리
            </a>
            <a href="{{ route('admin.moderation.forbidden-words') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ $current === 'admin.moderation.forbidden-words' ? 'bg-purple-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                <span>🔤</span> 금칙어 관리
            </a>
            <a href="{{ route('admin.moderation.policies') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ $current === 'admin.moderation.policies' ? 'bg-purple-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                <span>⚙️</span> 정책 설정
            </a>

            <p class="px-3 pt-4 pb-1 text-[10px] font-semibold text-gray-500 uppercase tracking-wider">로그 / 통계</p>

            <a href="{{ route('admin.logs.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ $current === 'admin.logs.index' ? 'bg-purple-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                <span>📋</span> 관리 로그
            </a>
            <a href="{{ route('admin.logs.clicks') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ $current === 'admin.logs.clicks' ? 'bg-purple-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                <span>👆</span> 클릭 로그
            </a>
            <a href="{{ route('admin.logs.recommendations') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ $current === 'admin.logs.recommendations' ? 'bg-purple-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                <span>🗺️</span> 추천 로그
            </a>
            <a href="{{ route('admin.logs.notifications') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ $current === 'admin.logs.notifications' ? 'bg-purple-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                <span>🔔</span> 알림 로그
            </a>
            <a href="{{ route('admin.access-logs.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ $current === 'admin.access-logs.index' ? 'bg-purple-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                <span>🌐</span> 접속 로그
            </a>
        </nav>
    </aside>

    {{-- 메인 영역 --}}
    <div class="transition-all duration-200" :class="sidebarOpen ? 'lg:ml-60' : 'ml-0'">
        {{-- 상단 바 --}}
        <header class="sticky top-0 z-20 h-14 bg-white border-b flex items-center justify-between px-4 shadow-sm">
            <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="flex items-center gap-4">
                <a href="{{ route('home') }}" target="_blank" class="text-sm text-gray-500 hover:text-purple-600">사이트 보기 ↗</a>
                <span class="text-sm text-gray-600">{{ auth()->user()->name }}</span>
                <form action="{{ route('admin.logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-sm text-red-500 hover:text-red-700">로그아웃</button>
                </form>
            </div>
        </header>

        {{-- 컨텐츠 --}}
        <main class="p-6">
            @if(session('success'))
                <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                    {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
    @include('layouts.partials.event-tracking')
    @stack('scripts')
</body>
</html>
