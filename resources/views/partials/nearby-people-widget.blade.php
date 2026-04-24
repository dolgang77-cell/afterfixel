@php
    $venueType = $venueType ?? 'club';
    $venueId = $venueId ?? null;
    $venueName = $venueName ?? '이 장소';
    $contextLabel = $contextLabel ?? '같은 장소';
@endphp

@if(config('nearby-messaging.enabled') && $venueId)
<div
    x-data="detailNearbyUsers({
        venueType: @js($venueType),
        venueId: {{ (int) $venueId }},
        venueName: @js($venueName),
        contextLabel: @js($contextLabel),
        authenticated: {{ auth()->check() ? 'true' : 'false' }},
        loginUrl: @js(route('login')),
        csrfToken: @js(csrf_token()),
    })"
    class="card p-4"
>
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-[14px] font-bold text-white">같은 장소 사람 보기</p>
            <p class="mt-1 text-[12px] leading-relaxed text-gray-400">
                정확한 위치는 공개하지 않고, 같은 장소 또는 내 근처 범위로만 보여줍니다.
            </p>
        </div>
        <span class="rounded-full border border-cyan-500/20 bg-cyan-500/10 px-3 py-1 text-[11px] font-semibold text-cyan-300">30분 만료 메시지</span>
    </div>

    <div class="mt-3 grid grid-cols-2 gap-2">
        <div class="rounded-2xl border border-white/[0.06] bg-dark-700/70 px-3 py-3">
            <p class="text-[11px] text-gray-500">노출 방식</p>
            <p class="mt-1 text-[13px] font-bold text-white">정확한 좌표 비공개</p>
            <p class="mt-1 text-[11px] text-gray-400">같은 장소 · 100m · 300m</p>
        </div>
        <div class="rounded-2xl border border-white/[0.06] bg-dark-700/70 px-3 py-3">
            <p class="text-[11px] text-gray-500">안전 장치</p>
            <p class="mt-1 text-[13px] font-bold text-white">차단 · 신고 가능</p>
            <p class="mt-1 text-[11px] text-gray-400">위치 공유는 직접 켜야 시작됩니다</p>
        </div>
    </div>

    <div class="mt-3 flex flex-wrap gap-2">
        <span class="rounded-full bg-white/5 px-3 py-1 text-[11px] font-semibold text-gray-300">기본 OFF</span>
        <span class="rounded-full bg-white/5 px-3 py-1 text-[11px] font-semibold text-gray-300">상호 차단 비노출</span>
        <span class="rounded-full bg-white/5 px-3 py-1 text-[11px] font-semibold text-gray-300">30분 후 자동 삭제</span>
    </div>

    <div class="mt-4 flex gap-2">
        <button
            type="button"
            @click="openPanel()"
            class="btn-secondary flex-1 rounded-2xl px-4 py-3 text-[12px] font-semibold text-white"
        >
            {{ auth()->check() ? '주변 사용자 열기' : '로그인 후 사용' }}
        </button>
        @auth
            <button
                type="button"
                @click="quickVenueShare()"
                class="rounded-2xl border border-white/[0.08] bg-dark-700/80 px-4 py-3 text-[12px] font-semibold text-gray-200"
            >
                이 장소 공유
            </button>
        @endauth
    </div>

    @auth
        <p class="mt-2 text-[11px] text-gray-500">위치 공유를 켜도 상대에게는 “같은 장소” 또는 “내 근처 100m/300m”만 보입니다.</p>
    @else
        <p class="mt-2 text-[11px] text-gray-500">로그인 후 위치 공유를 켜면 같은 장소 사용자와 1:1 메시지를 시작할 수 있습니다.</p>
    @endauth

    <template x-teleport="body">
        <div
            x-show="open"
            x-cloak
            class="fixed inset-0 z-[90]"
            x-transition.opacity.duration.200ms
        >
            <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="closePanel()"></div>

            <div class="absolute inset-x-0 bottom-0 mx-auto flex max-w-lg flex-col overflow-hidden rounded-t-[2rem] border border-white/[0.08] bg-dark-900 shadow-card" style="max-height: calc(100vh - 2.5rem);">
                <div class="flex items-center justify-between border-b border-white/[0.06] px-4 py-3">
                    <div>
                        <p class="text-[14px] font-bold text-white" x-text="sheetTitle"></p>
                        <p class="mt-0.5 text-[11px] text-gray-500" x-text="sheetSubtitle"></p>
                    </div>
                    <button type="button" @click="closePanel()" class="flex h-9 w-9 items-center justify-center rounded-full border border-white/[0.06] bg-white/5 text-gray-300">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="overflow-y-auto px-4 py-4">
                    <template x-if="notice">
                        <div class="mb-3 rounded-2xl border border-cyan-500/20 bg-cyan-500/10 px-3 py-2 text-[12px] text-cyan-200" x-text="notice"></div>
                    </template>
                    <template x-if="error">
                        <div class="mb-3 rounded-2xl border border-rose-500/20 bg-rose-500/10 px-3 py-2 text-[12px] text-rose-200" x-text="error"></div>
                    </template>

                    <div x-show="step === 'intro'" x-cloak class="space-y-3">
                        <div class="rounded-3xl border border-white/[0.06] bg-dark-800/80 p-4">
                            <p class="text-[14px] font-bold text-white">위치 공유를 켜면 바로 주변 사용자를 볼 수 있습니다</p>
                            <ul class="mt-3 space-y-2 text-[12px] leading-relaxed text-gray-400">
                                <li>정확한 GPS 좌표는 공개되지 않습니다.</li>
                                <li>메시지는 발송 후 30분 뒤 자동 삭제됩니다.</li>
                                <li>불쾌한 사용자는 차단하고 메시지를 신고할 수 있습니다.</li>
                            </ul>
                        </div>

                        <div class="rounded-3xl border border-white/[0.06] bg-dark-800/80 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-[13px] font-semibold text-white">외국인 모드만 보기</p>
                                    <p class="mt-1 text-[11px] text-gray-500">언어 교류를 원하는 사용자만 우선 확인합니다.</p>
                                </div>
                                <button
                                    type="button"
                                    @click="foreignOnly = !foreignOnly"
                                    class="relative h-7 w-12 rounded-full transition"
                                    :class="foreignOnly ? 'bg-cyan-500' : 'bg-white/10'"
                                >
                                    <span class="absolute top-1 h-5 w-5 rounded-full bg-white transition" :class="foreignOnly ? 'left-6' : 'left-1'"></span>
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-2">
                            <button type="button" @click="enableVenueOnly()" class="btn-primary rounded-2xl px-4 py-3 text-[13px] font-bold text-white">
                                이 장소에서만 공유 시작
                            </button>
                            <button type="button" @click="enableNearbyShare()" class="rounded-2xl border border-white/[0.08] bg-dark-700/80 px-4 py-3 text-[13px] font-semibold text-gray-200">
                                내 근처 범위까지 함께 보기
                            </button>
                            <button type="button" @click="closePanel()" class="rounded-2xl px-4 py-3 text-[12px] font-semibold text-gray-400">
                                지금은 닫기
                            </button>
                        </div>
                    </div>

                    <div x-show="step === 'list'" x-cloak class="space-y-3">
                        <div class="rounded-3xl border border-white/[0.06] bg-dark-800/80 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[13px] font-semibold text-white">현재 공개 상태</p>
                                    <p class="mt-1 text-[11px] text-gray-500" x-text="statusSummary"></p>
                                </div>
                                <button type="button" @click="disableShare()" class="rounded-full border border-white/[0.08] bg-white/5 px-3 py-1 text-[11px] font-semibold text-gray-200">
                                    공유 끄기
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-2">
                            <button type="button" @click="step = 'intro'" class="rounded-full border border-white/[0.08] bg-white/5 px-3 py-1.5 text-[11px] font-semibold text-gray-200">
                                설정 보기
                            </button>
                            <button type="button" @click="refreshUsers()" class="rounded-full border border-white/[0.08] bg-white/5 px-3 py-1.5 text-[11px] font-semibold text-gray-200">
                                새로고침
                            </button>
                        </div>

                        <template x-if="loadingUsers">
                            <div class="space-y-2">
                                <div class="h-24 animate-pulse rounded-3xl bg-white/5"></div>
                                <div class="h-24 animate-pulse rounded-3xl bg-white/5"></div>
                            </div>
                        </template>

                        <template x-if="!loadingUsers && users.length === 0">
                            <div class="rounded-3xl border border-dashed border-white/[0.08] px-4 py-8 text-center">
                                <p class="text-[14px] font-semibold text-white">지금 공개 중인 사용자가 없습니다</p>
                                <p class="mt-2 text-[12px] leading-relaxed text-gray-500">잠시 후 다시 새로고침하거나 위치를 다시 확인해 주세요.</p>
                            </div>
                        </template>

                        <div class="space-y-2" x-show="!loadingUsers && users.length > 0">
                            <template x-for="user in users" :key="user.user_id">
                                <button
                                    type="button"
                                    @click="openProfile(user)"
                                    class="flex w-full items-start gap-3 rounded-3xl border border-white/[0.06] bg-dark-800/80 p-3 text-left"
                                >
                                    <img :src="user.profile_image_url" alt="" class="h-14 w-14 rounded-2xl object-cover">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between gap-2">
                                            <p class="truncate text-[14px] font-semibold text-white" x-text="user.nickname"></p>
                                            <span class="rounded-full bg-cyan-500/10 px-2.5 py-1 text-[10px] font-semibold text-cyan-300" x-text="user.distance_label"></span>
                                        </div>
                                        <div class="mt-1 flex flex-wrap gap-1.5">
                                            <template x-if="user.foreign_mode">
                                                <span class="rounded-full bg-white/5 px-2 py-1 text-[10px] font-semibold text-sky-300">외국인 모드</span>
                                            </template>
                                            <template x-for="interest in (user.interests || []).slice(0, 2)" :key="interest">
                                                <span class="rounded-full bg-white/5 px-2 py-1 text-[10px] font-semibold text-gray-300" x-text="interest"></span>
                                            </template>
                                        </div>
                                        <p class="mt-2 text-[11px] text-gray-500" x-text="user.last_seen_at ? '방금 활동 · ' + user.area : user.area"></p>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div x-show="step === 'profile' && selectedUser" x-cloak class="space-y-3">
                        <button type="button" @click="step = 'list'" class="text-[12px] font-semibold text-gray-400">← 목록으로</button>

                        <div class="rounded-3xl border border-white/[0.06] bg-dark-800/80 p-4">
                            <div class="flex items-start gap-3">
                                <img :src="selectedUser?.profile_image_url" alt="" class="h-16 w-16 rounded-2xl object-cover">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <p class="truncate text-[16px] font-bold text-white" x-text="selectedUser?.nickname"></p>
                                        <span class="rounded-full bg-cyan-500/10 px-2.5 py-1 text-[10px] font-semibold text-cyan-300" x-text="selectedUser?.distance_label"></span>
                                    </div>
                                    <p class="mt-1 text-[12px] text-gray-500" x-text="selectedUser?.area || ''"></p>
                                    <div class="mt-2 flex flex-wrap gap-1.5">
                                        <template x-for="lang in (selectedUser?.languages || []).slice(0, 3)" :key="lang">
                                            <span class="rounded-full bg-white/5 px-2 py-1 text-[10px] font-semibold text-gray-300" x-text="lang"></span>
                                        </template>
                                        <template x-for="interest in (selectedUser?.interests || []).slice(0, 3)" :key="interest">
                                            <span class="rounded-full bg-white/5 px-2 py-1 text-[10px] font-semibold text-gray-300" x-text="interest"></span>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-2">
                            <button type="button" @click="startChat()" class="btn-primary rounded-2xl px-4 py-3 text-[13px] font-bold text-white">
                                메시지 시작하기
                            </button>
                            <button type="button" @click="blockSelectedUser()" class="rounded-2xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-[12px] font-semibold text-rose-200">
                                차단하기
                            </button>
                        </div>
                    </div>

                    <div x-show="step === 'chat' && selectedUser" x-cloak class="space-y-3">
                        <div class="rounded-3xl border border-cyan-500/15 bg-cyan-500/10 px-3 py-2 text-[11px] leading-relaxed text-cyan-100">
                            이 대화의 메시지는 발송 후 30분 뒤 자동 삭제됩니다. 캡처와 공유에 주의하세요.
                        </div>

                        <button type="button" @click="step = 'profile'" class="text-[12px] font-semibold text-gray-400">← 프로필로</button>

                        <div class="max-h-[42vh] space-y-2 overflow-y-auto rounded-3xl border border-white/[0.06] bg-dark-800/80 p-3">
                            <template x-if="loadingMessages">
                                <div class="space-y-2">
                                    <div class="ml-auto h-14 w-2/3 animate-pulse rounded-2xl bg-white/5"></div>
                                    <div class="h-14 w-2/3 animate-pulse rounded-2xl bg-white/5"></div>
                                </div>
                            </template>

                            <template x-if="!loadingMessages && messages.length === 0">
                                <div class="py-8 text-center">
                                    <p class="text-[13px] font-semibold text-white">첫 메시지를 보내보세요</p>
                                    <p class="mt-1 text-[11px] text-gray-500">공개된 범위 안에서만 안전하게 연결됩니다.</p>
                                </div>
                            </template>

                            <template x-for="message in messages" :key="message.id">
                                <div :class="message.is_mine ? 'flex justify-end' : 'flex justify-start'">
                                    <div class="max-w-[85%]">
                                        <div
                                            class="rounded-2xl px-3 py-2 text-[12px] leading-relaxed"
                                            :class="message.is_mine ? 'bg-cyan-500 text-white' : 'bg-white/8 text-gray-100'"
                                            x-text="message.body"
                                        ></div>
                                        <div class="mt-1 flex items-center gap-2" :class="message.is_mine ? 'justify-end' : 'justify-start'">
                                            <span class="text-[10px] text-gray-500" x-text="formatTime(message.created_at)"></span>
                                            <template x-if="!message.is_mine">
                                                <button type="button" @click="reportMessage(message)" class="text-[10px] font-semibold text-rose-300">신고</button>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="space-y-2">
                            <textarea
                                x-model="messageBody"
                                rows="3"
                                maxlength="500"
                                placeholder="인사만 짧게 시작하고, 개인정보는 바로 공유하지 마세요."
                                class="w-full rounded-3xl border border-white/[0.08] bg-dark-800 px-4 py-3 text-[13px] text-white placeholder:text-gray-500 focus:border-cyan-500/30 focus:outline-none"
                            ></textarea>
                            <div class="flex gap-2">
                                <button type="button" @click="leaveConversation()" class="rounded-2xl border border-white/[0.08] bg-dark-700/80 px-4 py-3 text-[12px] font-semibold text-gray-200">
                                    대화 나가기
                                </button>
                                <button type="button" @click="sendMessage()" class="btn-primary flex-1 rounded-2xl px-4 py-3 text-[13px] font-bold text-white">
                                    전송
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>

    @once
        @push('scripts')
        <script>
        window.detailNearbyUsers = window.detailNearbyUsers || function (config) {
            return {
                open: false,
                step: 'intro',
                users: [],
                selectedUser: null,
                messages: [],
                messageBody: '',
                conversationId: null,
                notice: '',
                error: '',
                loadingUsers: false,
                loadingMessages: false,
                loadingStatus: false,
                foreignOnly: false,
                hasActiveLocation: false,
                settings: null,
                status: null,
                pollTimer: null,

                get sheetTitle() {
                    if (this.step === 'chat' && this.selectedUser) {
                        return this.selectedUser.nickname + '와 대화';
                    }
                    if (this.step === 'profile' && this.selectedUser) {
                        return this.selectedUser.nickname + ' 프로필';
                    }
                    if (this.step === 'list') {
                        return config.contextLabel + ' 사용자';
                    }
                    return config.venueName + ' 주변 연결';
                },

                get sheetSubtitle() {
                    if (this.step === 'chat') {
                        return '메시지는 30분 뒤 자동 삭제됩니다.';
                    }
                    if (this.step === 'list') {
                        return '정확한 위치는 공개되지 않습니다.';
                    }
                    return '위치 공유는 직접 켜야 시작됩니다.';
                },

                get statusSummary() {
                    if (!this.settings) {
                        return '위치 공유 상태를 확인하는 중입니다.';
                    }

                    if (!this.settings.is_enabled || !this.settings.is_visible) {
                        return '현재는 주변 사용자에게 보이지 않는 상태입니다.';
                    }

                    return this.settings.share_scope === 'nearby'
                        ? '내 근처 300m 범위까지 공개 중입니다.'
                        : '현재 장소에서만 공개 중입니다.';
                },

                async openPanel() {
                    if (!config.authenticated) {
                        window.location.href = config.loginUrl;
                        return;
                    }

                    this.open = true;
                    this.error = '';
                    this.notice = '';
                    this.step = 'intro';
                    await this.fetchStatus();

                    if (this.settings?.is_enabled && this.hasActiveLocation) {
                        this.step = 'list';
                        await this.refreshUsers();
                    }
                },

                closePanel() {
                    this.open = false;
                    this.step = 'intro';
                    this.selectedUser = null;
                    this.messages = [];
                    this.messageBody = '';
                    this.conversationId = null;
                    this.stopPolling();
                },

                async quickVenueShare() {
                    await this.openPanel();
                    if (config.authenticated) {
                        await this.enableVenueOnly();
                    }
                },

                async enableVenueOnly() {
                    await this.activateSharing('venue_only');
                },

                async enableNearbyShare() {
                    await this.activateSharing('nearby');
                },

                async activateSharing(shareScope) {
                    this.error = '';
                    this.notice = '';

                    try {
                        await this.patchJson('/api/nearby-users/settings', {
                            is_enabled: true,
                            is_visible: true,
                            share_scope: shareScope,
                        });

                        await this.requestLocationUpdate();
                        await this.fetchStatus();
                        await this.refreshUsers();
                        this.step = 'list';
                        this.notice = shareScope === 'nearby'
                            ? '내 근처 범위 공유를 시작했습니다.'
                            : '이 장소에서만 공유를 시작했습니다.';
                    } catch (error) {
                        this.error = error.message;
                    }
                },

                async disableShare() {
                    try {
                        await this.patchJson('/api/nearby-users/settings', {
                            is_enabled: false,
                            is_visible: false,
                            share_scope: 'off',
                        });
                        await this.fetchStatus();
                        this.users = [];
                        this.step = 'intro';
                        this.notice = '위치 공유를 종료했습니다.';
                    } catch (error) {
                        this.error = error.message;
                    }
                },

                async fetchStatus() {
                    this.loadingStatus = true;

                    try {
                        const payload = await this.fetchJson('/api/nearby-users/status');
                        this.settings = payload.data?.settings || null;
                        this.status = payload.data?.location_status || null;
                        this.hasActiveLocation = payload.data?.has_active_location || false;
                    } catch (error) {
                        this.error = error.message;
                    } finally {
                        this.loadingStatus = false;
                    }
                },

                async requestLocationUpdate() {
                    if (!navigator.geolocation) {
                        throw new Error('이 기기에서는 위치 공유를 사용할 수 없습니다.');
                    }

                    const position = await new Promise((resolve, reject) => {
                        navigator.geolocation.getCurrentPosition(resolve, reject, {
                            enableHighAccuracy: true,
                            timeout: 10000,
                            maximumAge: 60000,
                        });
                    }).catch(() => {
                        throw new Error('위치를 가져오지 못했습니다. 브라우저 권한을 확인해 주세요.');
                    });

                    await this.postJson('/api/nearby-users/location', {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                        accuracy_m: position.coords.accuracy,
                        venue_type: config.venueType,
                        venue_id: config.venueId,
                        source: 'detail_page',
                    });
                },

                async refreshUsers() {
                    this.loadingUsers = true;
                    this.error = '';

                    try {
                        const query = new URLSearchParams();
                        if (this.foreignOnly) {
                            query.set('foreign_only', '1');
                        }

                        const payload = await this.fetchJson('/api/nearby-users' + (query.toString() ? '?' + query.toString() : ''));
                        this.users = payload.data || [];
                    } catch (error) {
                        this.error = error.message;
                    } finally {
                        this.loadingUsers = false;
                    }
                },

                openProfile(user) {
                    this.selectedUser = user;
                    this.step = 'profile';
                },

                async startChat() {
                    if (!this.selectedUser) {
                        return;
                    }

                    try {
                        const payload = await this.postJson('/api/conversations', {
                            recipient_user_id: this.selectedUser.user_id,
                        });

                        this.conversationId = payload.data?.id || null;
                        this.step = 'chat';
                        await this.loadConversation();
                        this.startPolling();
                    } catch (error) {
                        this.error = error.message;
                    }
                },

                async loadConversation() {
                    if (!this.conversationId) {
                        return;
                    }

                    this.loadingMessages = true;

                    try {
                        const payload = await this.fetchJson('/api/conversations/' + this.conversationId);
                        const conversation = payload.data?.conversation || {};
                        const messages = payload.data?.messages || [];

                        if (conversation.other_user) {
                            this.selectedUser = {
                                ...this.selectedUser,
                                user_id: conversation.other_user.id,
                                nickname: conversation.other_user.nickname,
                                profile_image_url: conversation.other_user.profile_image_url,
                            };
                        }

                        this.messages = messages;
                        await this.postJson('/api/conversations/' + this.conversationId + '/read', {});
                    } catch (error) {
                        this.error = error.message;
                    } finally {
                        this.loadingMessages = false;
                    }
                },

                async sendMessage() {
                    if (!this.conversationId) {
                        return;
                    }

                    const body = (this.messageBody || '').trim();
                    if (!body) {
                        this.error = '메시지를 입력해 주세요.';
                        return;
                    }

                    try {
                        await this.postJson('/api/conversations/' + this.conversationId + '/messages', {
                            body: body,
                        });

                        this.messageBody = '';
                        await this.loadConversation();
                        this.notice = '메시지를 보냈습니다. 30분 뒤 자동 삭제됩니다.';
                    } catch (error) {
                        this.error = error.message;
                    }
                },

                async leaveConversation() {
                    if (!this.conversationId) {
                        this.step = 'profile';
                        return;
                    }

                    try {
                        await this.postJson('/api/conversations/' + this.conversationId + '/leave', {});
                        this.stopPolling();
                        this.step = 'profile';
                        this.notice = '대화방에서 나갔습니다.';
                    } catch (error) {
                        this.error = error.message;
                    }
                },

                async reportMessage(message) {
                    if (!message || !message.id) {
                        return;
                    }

                    const detail = window.prompt('신고 사유를 짧게 남겨 주세요. 자세한 내용이 없으면 비워두고 확인을 누르세요.', '');

                    try {
                        await this.postJson('/api/messages/' + message.id + '/report', {
                            reason: 'abuse',
                            detail: detail || '',
                        });
                        this.notice = '신고가 접수되었습니다.';
                    } catch (error) {
                        this.error = error.message;
                    }
                },

                async blockSelectedUser() {
                    if (!this.selectedUser?.user_id) {
                        return;
                    }

                    if (!window.confirm('이 사용자를 차단할까요? 차단하면 서로 보이지 않습니다.')) {
                        return;
                    }

                    try {
                        await this.postJson('/api/users/' + this.selectedUser.user_id + '/block', {
                            reason: 'detail_block',
                        });
                        this.notice = '사용자를 차단했습니다.';
                        this.users = this.users.filter((user) => user.user_id !== this.selectedUser.user_id);
                        this.step = 'list';
                    } catch (error) {
                        this.error = error.message;
                    }
                },

                startPolling() {
                    this.stopPolling();
                    this.pollTimer = setInterval(() => {
                        if (this.open && this.step === 'chat' && this.conversationId) {
                            this.loadConversation();
                        }
                    }, 10000);
                },

                stopPolling() {
                    if (this.pollTimer) {
                        clearInterval(this.pollTimer);
                        this.pollTimer = null;
                    }
                },

                async fetchJson(url) {
                    const response = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                    });

                    return this.handleResponse(response);
                },

                async postJson(url, body) {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': config.csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify(body || {}),
                    });

                    return this.handleResponse(response);
                },

                async patchJson(url, body) {
                    const response = await fetch(url, {
                        method: 'PATCH',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': config.csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify(body || {}),
                    });

                    return this.handleResponse(response);
                },

                async handleResponse(response) {
                    const data = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        const details = data.details ? Object.values(data.details).flat().join(' ') : '';
                        throw new Error(details || data.error || '요청을 처리하지 못했습니다.');
                    }

                    return data;
                },

                formatTime(value) {
                    if (!value) {
                        return '';
                    }

                    try {
                        return new Date(value).toLocaleTimeString('ko-KR', {
                            hour: '2-digit',
                            minute: '2-digit',
                        });
                    } catch (error) {
                        return '';
                    }
                },
            };
        };
        </script>
        @endpush
    @endonce
</div>
@endif
