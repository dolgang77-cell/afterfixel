@extends('layouts.app')
@section('title', 'MY')
@section('page_type', 'my_page')

@section('content')
<div class="px-4 py-6 space-y-5"
     @keydown.escape.window="closeProfileModal()"
     x-data="profileImageUploader({
         uploadUrl: @js(route('profile-image.store')),
         csrfToken: @js(csrf_token()),
         isAuthenticated: @js(auth()->check()),
         maxUploadBytes: @js(10 * 1024 * 1024),
         defaultImageUrl: @js($defaultProfileImageUrl ?? asset('images/default-profile.svg')),
         currentImageUrl: @js($displayProfileImage?->thumb_url ?? $defaultProfileImageUrl ?? asset('images/default-profile.svg')),
         previewUrl: @js($latestProfileImage?->thumb_url ?? $displayProfileImage?->thumb_url ?? $defaultProfileImageUrl ?? asset('images/default-profile.svg')),
         initialStatus: @js($latestProfileImage?->status ?? 'none'),
         initialMessage: @js(
            auth()->check()
                ? match ($latestProfileImage?->status) {
                    'approved' => '현재 이미지가 공개 중입니다.',
                    'pending' => '관리자 승인 후 적용됩니다.',
                    'rejected' => $latestProfileImage?->rejection_reason ?: '이미지가 반려되었습니다.',
                    default => '첫 프로필 이미지를 등록해 보세요.',
                }
                : ''
         ),
         rejectedReason: @js($latestProfileImage?->status === 'rejected' ? $latestProfileImage->rejection_reason : null),
     })">
    {{-- 프로필 헤더 --}}
    <div class="card p-5">
        <div class="flex items-start gap-4">
            <button type="button"
                    class="relative w-20 h-20 rounded-[22px] overflow-hidden bg-dark-700 border border-white/[0.08] shrink-0 shadow-glow text-left"
                    @click="openProfileModal()">
                <img src="{{ $displayProfileImage?->thumb_url ?? $defaultProfileImageUrl }}" :src="currentImageUrl" alt="profile image" class="w-full h-full object-cover">
                <div x-show="uploading" x-cloak class="absolute inset-0 bg-dark-950/75 flex items-center justify-center">
                    <div class="w-8 h-8 rounded-full border-2 border-white/20 border-t-white animate-spin"></div>
                </div>
            </button>
            <div class="flex-1 min-w-0">
                @auth
                    <div class="min-w-0">
                        <p class="text-[16px] font-bold text-white">{{ auth()->user()->name }}</p>
                        <p class="text-[12px] text-gray-500 mt-0.5 truncate">{{ auth()->user()->email }}</p>
                    </div>
                    <div class="mt-3 space-y-2">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="text-[13px] font-semibold text-gray-100">프로필 이미지</p>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold"
                                  :class="statusBadgeClass()"
                                  x-text="statusBadgeLabel()"></span>
                        </div>
                        <p x-show="errorMessage" x-cloak class="text-[11px] text-red-300" x-text="errorMessage"></p>
                    </div>
                @else
                    <p class="text-[16px] font-bold text-white">{{ __('my.title') }}</p>
                    <p class="text-[12px] text-gray-500 mt-0.5">{{ __('my.guest_mode') }}</p>
                @endauth
            </div>
        </div>

        {{-- 통계 --}}
        <div class="grid grid-cols-3 gap-3 mt-4 pt-4 border-t border-white/[0.04]">
            <a href="{{ route('favorites.index') }}" class="text-center">
                <p class="text-[18px] font-bold text-accent">{{ $favCount }}</p>
                <p class="text-[11px] text-gray-500 mt-0.5">{{ __('my.favorites') }}</p>
            </a>
            <a href="{{ route('my.recent') }}" class="text-center">
                <p class="text-[18px] font-bold text-pink-400">{{ $recentCount }}</p>
                <p class="text-[11px] text-gray-500 mt-0.5">{{ __('my.recent') }}</p>
            </a>
            <a href="{{ route('notifications.index') }}" class="text-center relative">
                <p class="text-[18px] font-bold text-blue-400">{{ $unreadCount }}</p>
                <p class="text-[11px] text-gray-500 mt-0.5">{{ __('nav.notifications') }}</p>
            </a>
        </div>
    </div>

    @auth
        @include('partials.revisit-hub', [
            'hub' => $revisitHub,
            'surface' => 'my',
            'title' => '지금 해야 할 일',
            'subtitle' => '답변 확인, 이어보기, 찜, 알림을 같은 기준으로 한 번에 처리합니다.',
            'showAlways' => true,
        ])
    @endauth

    {{-- 빠른 메뉴 --}}
    <div class="grid grid-cols-2 gap-2.5">
        <a href="{{ route('favorites.index') }}" class="card p-4 flex items-center gap-3 group" data-track-event="my_action_card_click" data-track-context="favorites" data-track-label="찜 목록">
            <div class="w-9 h-9 rounded-xl bg-pink-500/10 flex items-center justify-center text-[16px] group-active:scale-90 transition-transform">❤️</div>
            <div>
                <p class="text-[13px] font-semibold text-gray-200">{{ __('my.fav_list') }}</p>
                <p class="text-[10px] text-gray-500">{{ __('my.fav_desc') }}</p>
            </div>
        </a>
        <a href="{{ route('my.recent') }}" class="card p-4 flex items-center gap-3 group" data-track-event="my_action_card_click" data-track-context="recent" data-track-label="최근 본 전체">
            <div class="w-9 h-9 rounded-xl bg-blue-500/10 flex items-center justify-center text-[16px] group-active:scale-90 transition-transform">🕐</div>
            <div>
                <p class="text-[13px] font-semibold text-gray-200">최근 본 전체</p>
                <p class="text-[10px] text-gray-500">최근 본 기록을 모아서 확인합니다.</p>
            </div>
        </a>
        <a href="{{ route('my.preferences') }}" class="card p-4 flex items-center gap-3 group" data-track-event="my_action_card_click" data-track-context="preferences" data-track-label="관심 설정">
            <div class="w-9 h-9 rounded-xl bg-purple-500/10 flex items-center justify-center text-[16px] group-active:scale-90 transition-transform">⚙️</div>
            <div>
                <p class="text-[13px] font-semibold text-gray-200">{{ __('my.preferences') }}</p>
                <p class="text-[10px] text-gray-500">{{ __('my.pref_desc') }}</p>
            </div>
        </a>
        <a href="{{ route('notification-settings.edit') }}" class="card p-4 flex items-center gap-3 group" data-track-event="my_action_card_click" data-track-context="notification_settings" data-track-label="알림 설정">
            <div class="w-9 h-9 rounded-xl bg-green-500/10 flex items-center justify-center text-[16px] group-active:scale-90 transition-transform">🔔</div>
            <div>
                <p class="text-[13px] font-semibold text-gray-200">{{ __('my.notification_settings') }}</p>
                <p class="text-[10px] text-gray-500">{{ __('my.notif_desc') }}</p>
            </div>
        </a>
    </div>

    @auth
        @if(auth()->user()->isMd() && auth()->user()->isActive() && auth()->user()->mdProfile)
            <a href="{{ route('md-dashboard.index') }}" class="card p-4 flex items-center gap-3 group border border-indigo-500/20" data-track-event="my_action_card_click" data-track-context="md_dashboard" data-track-label="MD 작업실">
                <div class="w-10 h-10 rounded-2xl bg-indigo-500/15 flex items-center justify-center text-[16px]">🎧</div>
                <div class="flex-1">
                    <p class="text-[13px] font-semibold text-indigo-200">MD 작업실</p>
                    <p class="text-[10px] text-gray-500">담당 클럽/파티 수정, 이미지 업로드, 문의 답변</p>
                </div>
                <svg class="w-4 h-4 text-indigo-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            </a>
        @endif
    @endauth

    {{-- 문의내역 --}}
    @auth
    <a href="{{ route('my.inquiries') }}" class="card p-4 flex items-center gap-3 group" data-track-event="my_action_card_click" data-track-context="inquiries" data-track-label="문의내역">
        <div class="w-9 h-9 rounded-xl bg-cyan-500/10 flex items-center justify-center text-[16px]">💬</div>
        <div class="flex-1">
            <p class="text-[13px] font-semibold text-gray-200">{{ __('inquiry.my_inquiries') }}</p>
            <p class="text-[10px] text-gray-500">{{ __('my.inquiry_desc') }}</p>
        </div>
        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
    </a>
    @endauth

    {{-- 커뮤니티 링크 --}}
    <a href="{{ route('community.index') }}" class="card p-4 flex items-center gap-3 group" data-track-event="my_action_card_click" data-track-context="community" data-track-label="커뮤니티">
        <div class="w-9 h-9 rounded-xl bg-orange-500/10 flex items-center justify-center text-[16px]">💬</div>
        <div class="flex-1">
            <p class="text-[13px] font-semibold text-gray-200">{{ __('community.title') }}</p>
            <p class="text-[10px] text-gray-500">{{ __('my.community_desc') }}</p>
        </div>
        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
    </a>

    {{-- 안내 / 로그인·로그아웃 --}}
    @auth
    <div class="space-y-2">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="card w-full p-4 flex items-center gap-3 group text-left">
                <div class="w-9 h-9 rounded-xl bg-red-500/10 flex items-center justify-center text-[16px]">🚪</div>
                <div class="flex-1">
                    <p class="text-[13px] font-semibold text-gray-200">{{ __('common.logout') }}</p>
                </div>
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            </button>
        </form>
    </div>
    @else
    <div class="card p-5 text-center space-y-3">
        <p class="text-[13px] text-gray-400">{{ __('my.login_prompt') }}</p>
        <div class="flex gap-2.5">
            <a href="{{ route('login') }}" class="flex-1 btn-primary py-3 rounded-2xl text-[13px] font-bold text-white text-center">{{ __('common.login') }}</a>
            <a href="{{ route('register') }}" class="flex-1 py-3 rounded-2xl text-[13px] font-bold text-gray-300 text-center border border-white/[0.08] bg-dark-700">{{ __('common.register') }}</a>
        </div>
    </div>
    <div class="text-center pt-2">
        <p class="text-[11px] text-gray-600">{{ __('my.guest_notice') }}</p>
        <p class="text-[10px] text-gray-700 mt-1">{{ __('my.guest_warning') }}</p>
    </div>
    @endauth

    @auth
    <div x-show="showProfileModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-end justify-center bg-black/72 px-4 pb-4 pt-12 backdrop-blur-sm sm:items-center sm:pb-6"
         @click.self="closeProfileModal()">
        <div class="w-full max-w-sm overflow-hidden rounded-[34px] border border-white/[0.08] bg-dark-900 shadow-2xl">
            <div class="flex items-center justify-between px-5 pt-5">
                <div>
                    <p class="text-[12px] font-semibold tracking-[0.18em] text-gray-500">PROFILE</p>
                    <p class="mt-1 text-[18px] font-bold text-white">{{ auth()->user()->name }}</p>
                </div>
                <button type="button"
                        class="flex h-9 w-9 items-center justify-center rounded-full bg-white/[0.06] text-gray-300"
                        @click="closeProfileModal()">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="px-5 pb-5 pt-4">
                <div class="rounded-[30px] border border-white/[0.06] bg-dark-800 px-5 pb-5 pt-6 text-center">
                    <div class="mx-auto h-48 w-48 overflow-hidden rounded-full border border-white/[0.08] bg-dark-700 shadow-[0_18px_40px_rgba(0,0,0,0.35)]">
                        <img :src="modalImageUrl()" alt="profile preview" class="h-full w-full object-cover">
                    </div>

                    <div class="mt-4 flex items-center justify-center gap-2">
                        <span class="rounded-full px-2.5 py-1 text-[10px] font-semibold"
                              :class="statusBadgeClass()"
                              x-text="statusBadgeLabel()"></span>
                        <span x-show="file" x-cloak class="rounded-full bg-accent/15 px-2.5 py-1 text-[10px] font-semibold text-accent">변경 준비됨</span>
                    </div>

                    <p class="mt-3 text-[13px] font-semibold text-white" x-text="previewTitle()"></p>
                    <p class="mt-1 text-[11px] leading-5 text-gray-400" x-text="previewDescription() || statusMessage"></p>
                    <p x-show="rejectedReason" x-cloak class="mt-2 text-[11px] leading-5 text-red-300" x-text="rejectedReason"></p>

                    <div class="mt-5 grid grid-cols-1 gap-2">
                        <label class="inline-flex cursor-pointer items-center justify-center rounded-2xl border border-accent/30 bg-dark-700 px-4 py-3 text-[13px] font-bold text-accent shadow-glow-sm">
                            사진 변경
                            <input x-ref="fileInput" type="file" accept="image/jpeg,image/png,image/webp" class="hidden" @change="selectFile">
                        </label>
                        <button type="button"
                                class="gradient-accent rounded-2xl px-4 py-3 text-[13px] font-bold text-white shadow-glow-sm disabled:cursor-not-allowed disabled:opacity-40"
                                :disabled="!file || uploading"
                                @click="upload">
                            업로드
                        </button>
                        <button type="button"
                                x-show="file"
                                x-cloak
                                class="rounded-2xl border border-white/[0.08] bg-dark-700 px-4 py-3 text-[13px] font-bold text-gray-200"
                                @click="resetSelection">
                            선택 취소
                        </button>
                    </div>

                    <p class="mt-4 text-[10px] text-gray-500">JPG, PNG, WEBP · 최대 10MB · 업로드 후 자동 최적화 및 검수</p>
                </div>
            </div>
        </div>
    </div>
    @endauth
</div>
@endsection

@push('scripts')
<script>
function profileImageUploader(config) {
    return {
        uploadUrl: config.uploadUrl,
        csrfToken: config.csrfToken,
        isAuthenticated: config.isAuthenticated,
        maxUploadBytes: config.maxUploadBytes || 10485760,
        defaultImageUrl: config.defaultImageUrl,
        currentImageUrl: config.currentImageUrl || config.defaultImageUrl,
        serverPreviewUrl: config.previewUrl || config.currentImageUrl || config.defaultImageUrl,
        previewUrl: config.previewUrl || config.currentImageUrl || config.defaultImageUrl,
        status: config.initialStatus || 'none',
        statusMessage: config.initialMessage || '',
        rejectedReason: config.rejectedReason || '',
        uploading: false,
        showProfileModal: false,
        file: null,
        selectedName: '',
        errorMessage: '',

        openProfileModal() {
            this.showProfileModal = true;
            document.body.classList.add('overflow-hidden');
        },

        closeProfileModal() {
            this.showProfileModal = false;
            document.body.classList.remove('overflow-hidden');
        },

        selectFile(event) {
            const selected = event.target.files && event.target.files[0];
            if (!selected) {
                return;
            }

            const isSupportedType = ['image/jpeg', 'image/png', 'image/webp'].includes(selected.type)
                || /\.(jpe?g|png|webp)$/i.test(selected.name || '');

            if (!isSupportedType) {
                this.errorMessage = 'JPG, PNG, WEBP 파일만 업로드할 수 있습니다.';
                this.resetSelection();
                return;
            }

            if (selected.size > this.maxUploadBytes) {
                this.errorMessage = '최대 10MB 이미지까지 업로드할 수 있습니다.';
                this.resetSelection();
                return;
            }

            this.file = selected;
            this.selectedName = selected.name;
            this.errorMessage = '';
            this.rejectedReason = '';

            const reader = new FileReader();
            reader.onload = (loadEvent) => {
                this.previewUrl = loadEvent.target && loadEvent.target.result
                    ? loadEvent.target.result
                    : this.previewUrl;
            };
            reader.readAsDataURL(selected);
        },

        resetSelection() {
            this.file = null;
            this.selectedName = '';
            this.previewUrl = this.serverPreviewUrl || this.currentImageUrl || this.defaultImageUrl;

            if (this.$refs.fileInput) {
                this.$refs.fileInput.value = '';
            }
        },

        async upload() {
            if (!this.file || this.uploading || !this.isAuthenticated) {
                return;
            }

            this.uploading = true;
            this.errorMessage = '';
            this.statusMessage = '이미지를 최적화하고 검수 중입니다...';

            const formData = new FormData();
            formData.append('image', this.file);

            try {
                const response = await fetch(this.uploadUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const payload = await response.json();

                if (!response.ok) {
                    const detailMessage = payload.details ? Object.values(payload.details)[0]?.[0] : null;
                    throw new Error(detailMessage || payload.error || payload.message || '업로드에 실패했습니다.');
                }

                this.status = payload.status || 'pending';
                this.statusMessage = payload.message || '처리가 완료되었습니다.';
                this.rejectedReason = payload.rejection_reason || '';
                this.serverPreviewUrl = payload.thumb_url || payload.image_url || this.serverPreviewUrl;
                this.previewUrl = this.serverPreviewUrl;

                if (payload.status === 'approved') {
                    this.currentImageUrl = payload.current_thumb_url || payload.current_image_url || this.previewUrl;
                    this.serverPreviewUrl = this.currentImageUrl;
                    this.previewUrl = this.currentImageUrl;
                }

                this.resetSelection();
            } catch (error) {
                this.errorMessage = error.message || '업로드에 실패했습니다.';
            } finally {
                this.uploading = false;
            }
        },

        statusBadgeClass() {
            return {
                'bg-green-500/15 text-green-300': this.status === 'approved',
                'bg-yellow-500/15 text-yellow-200': this.status === 'pending',
                'bg-red-500/15 text-red-300': this.status === 'rejected',
                'bg-white/[0.06] text-gray-400': !['approved', 'pending', 'rejected'].includes(this.status),
            };
        },

        statusBadgeLabel() {
            if (this.status === 'approved') return '공개 중';
            if (this.status === 'pending') return '승인 대기';
            if (this.status === 'rejected') return '반려됨';

            return '미등록';
        },

        modalImageUrl() {
            if (this.file) {
                return this.previewUrl;
            }

            if (['pending', 'rejected'].includes(this.status)) {
                return this.serverPreviewUrl || this.currentImageUrl || this.defaultImageUrl;
            }

            return this.currentImageUrl || this.defaultImageUrl;
        },

        previewTitle() {
            if (this.file) return '업로드 예정 이미지';
            if (this.status === 'pending') return '승인 대기 중인 프로필 사진';
            if (this.status === 'rejected') return '최근 반려된 프로필 사진';

            return '현재 프로필 사진';
        },

        previewDescription() {
            if (this.status === 'pending') return '현재 공개 사진은 유지되고, 승인되면 새 사진으로 교체됩니다.';
            if (this.status === 'rejected') return '반려 사유를 확인한 뒤 새 사진으로 다시 업로드할 수 있습니다.';

            return '프로필 사진을 눌러 카카오톡처럼 크게 보고 변경할 수 있습니다.';
        },
    };
}
</script>
@endpush
