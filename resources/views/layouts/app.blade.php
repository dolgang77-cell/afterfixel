<!DOCTYPE html>
<html lang="ko" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#07070A">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="VYBE">
    <title>VYBE · @yield('title', '오늘 밤, 어디로 갈까')</title>
    <meta name="description" content="@yield('description', '전국 클럽·파티·투어를 한 번에 찾는 나이트 아웃 가이드.')">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Open Graph --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="VYBE">
    <meta property="og:title" content="VYBE · @yield('title', '오늘 밤, 어디로 갈까')">
    <meta property="og:description" content="@yield('description', '전국 클럽·파티·투어를 한 번에 찾는 나이트 아웃 가이드.')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="@yield('og_image', url('/app-icons/icon-512x512.png'))">
    <meta property="og:locale" content="ko_KR">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="VYBE · @yield('title', '오늘 밤, 어디로 갈까')">
    <meta name="twitter:description" content="@yield('description', '전국 클럽·파티·투어를 한 번에 찾는 나이트 아웃 가이드.')">
    <meta name="twitter:image" content="@yield('og_image', url('/app-icons/icon-512x512.png'))">

    {{-- PWA --}}
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/app-icons/apple-touch-icon.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/app-icons/icon-152x152.png">
    <link rel="apple-touch-icon" sizes="192x192" href="/app-icons/icon-192x192.png">
    <link rel="icon" type="image/svg+xml" href="/app-icons/icon.svg">
    <link rel="icon" type="image/png" sizes="192x192" href="/app-icons/icon-192x192.png">

    {{-- Local frontend runtime assets for intranet deployment --}}
    <script src="/vendor/tailwindcss-3.4.17.js"></script>
    <script>
        // wvybe design system — dark stage, violet (#5D5EFC) + cyan (#38D7ED)
        // NOTE: skin only. All class names below stay identical to the original
        // so every Blade view (bg-dark-950, text-accent, shadow-glow 등) 픽업만 바뀌게 한다.
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: {
                            950: '#07070A',  // page background (ink-0)
                            900: '#0D0D15',  // raised background (ink-10)
                            800: '#1C1E30',  // surface (ink-20)
                            700: '#282941',  // elevated surface (ink-30)
                            600: '#404162',  // border strong (ink-60)
                            500: '#2D2D4B',  // dashed border (ink-40)
                        },
                        neon: {
                            purple: '#5D5EFC',  // violet (brand primary)
                            pink:   '#6061FF',  // violet-2 (legacy alias — no pink in brand)
                            blue:   '#5D5EFC',
                            green:  '#38D7ED',  // cyan accent
                            orange: '#6061FF',
                            cyan:   '#38D7ED',
                        },
                        accent: {
                            DEFAULT: '#5D5EFC',  // violet
                            light:   '#6061FF',  // hover
                            dark:    '#3535B0',  // pressed
                        }
                    },
                    fontFamily: {
                        sans: ['Pretendard', '-apple-system', 'BlinkMacSystemFont', 'system-ui', 'sans-serif'],
                    },
                    borderRadius: {
                        '2xl': '1rem',
                        '3xl': '1.5rem',
                    },
                    boxShadow: {
                        'glow-sm':    '0 0 15px -3px rgba(93, 94, 252, 0.35)',
                        'glow':       '0 0 25px -5px rgba(93, 94, 252, 0.45)',
                        'glow-lg':    '0 20px 60px 0 rgba(93, 94, 252, 0.45)',
                        'glow-pink':  '0 0 25px -5px rgba(56, 215, 237, 0.4)', // remapped to cyan
                        'card':       '0 12px 24px -4px rgba(0, 0, 0, 0.35)',
                        'card-hover': '0 24px 48px -8px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(96, 97, 255, 0.25)',
                    },
                }
            }
        }
    </script>

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

    @include('layouts.partials.styles')
    @stack('styles')
</head>
<body class="bg-dark-950 text-gray-100 font-sans min-h-full dark antialiased" data-page-type="@yield('page_type', 'site')">

    <x-layout.header />

    <main class="pt-14 content-pb max-w-lg mx-auto relative">
        <x-layout.flash-messages />
        @yield('content')
    </main>

    <x-layout.bottom-nav />

    {{-- PWA: Service Worker 등록 + 설치 배너 --}}
    <div id="pwa-install"
         class="fixed bottom-[76px] left-4 right-4 max-w-lg mx-auto z-50 hidden"
         style="padding-bottom: env(safe-area-inset-bottom);">
        <div class="glass rounded-2xl border border-white/[0.06] p-4 flex items-center gap-3 shadow-card">
            <img src="/app-icons/icon.svg" class="w-10 h-10 rounded-xl shrink-0" alt="VYBE">
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-white">{{ __('pwa.install_title') }}</p>
                <p class="text-xs text-gray-400">{{ __('pwa.install_desc') }}</p>
            </div>
            <button id="pwa-install-btn" class="btn-primary px-4 py-2 rounded-xl text-xs font-bold text-white shrink-0">{{ __('pwa.install_btn') }}</button>
            <button id="pwa-dismiss-btn" class="text-gray-500 hover:text-gray-300 p-1 shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

    <script>
    // Service Worker 등록
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js?v=20260422-081930').catch(() => {});
        });
    }

    // PWA 설치 프롬프트
    (function() {
        let deferredPrompt = null;
        const banner = document.getElementById('pwa-install');
        const installBtn = document.getElementById('pwa-install-btn');
        const dismissBtn = document.getElementById('pwa-dismiss-btn');

        // 이미 설치했거나 닫기 누른 경우 숨김
        const isInstalled = window.matchMedia('(display-mode: standalone)').matches
                         || window.navigator.standalone === true;
        const isDismissed = sessionStorage.getItem('pwa-dismissed');

        if (isInstalled || isDismissed) return;

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            banner.classList.remove('hidden');
        });

        installBtn?.addEventListener('click', async () => {
            if (!deferredPrompt) return;
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            deferredPrompt = null;
            banner.classList.add('hidden');
        });

        dismissBtn?.addEventListener('click', () => {
            banner.classList.add('hidden');
            sessionStorage.setItem('pwa-dismissed', '1');
        });

        // 설치 완료 시 배너 숨김
        window.addEventListener('appinstalled', () => {
            banner.classList.add('hidden');
            deferredPrompt = null;
        });
    })();
    </script>

    {{-- 알림 벨 컴포넌트 --}}
    <script>
    function notifBell() {
        return {
            count: 0,
            checkUnread() {
                fetch('/notifications/unread-count', {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(d => this.count = d.count || 0)
                .catch(() => {});
            }
        };
    }
    </script>

    {{-- 위치 감지 컴포넌트 --}}
    @php
        $locationAreas = collect(\App\Services\GeoService::AREA_CENTERS)
            ->map(fn (array $center, string $name) => [
                'name' => $name,
                'lat' => $center['lat'],
                'lng' => $center['lng'],
            ])
            ->values()
            ->all();
    @endphp
    <script>
    function locationBtn() {
        var areas = @json($locationAreas);
        function distanceKm(lat1, lng1, lat2, lng2) {
            var toRad = function(value) { return value * Math.PI / 180; };
            var earthRadius = 6371;
            var dLat = toRad(lat2 - lat1);
            var dLng = toRad(lng2 - lng1);
            var a = Math.sin(dLat / 2) * Math.sin(dLat / 2)
                + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2))
                * Math.sin(dLng / 2) * Math.sin(dLng / 2);
            return earthRadius * (2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a)));
        }
        return {
            areaName: localStorage.getItem('nite_area') || '{{ __("nav.location") }}',
            loading: false,
            refresh: function() {
                if (!navigator.geolocation) return;
                this.loading = true;
                var self = this;
                navigator.geolocation.getCurrentPosition(
                    function(pos) {
                        var lat = pos.coords.latitude, lng = pos.coords.longitude;
                        localStorage.setItem('nite_lat', lat);
                        localStorage.setItem('nite_lng', lng);
                        var best = null, bestDist = Number.POSITIVE_INFINITY;
                        for (var i = 0; i < areas.length; i++) {
                            var d = distanceKm(lat, lng, areas[i].lat, areas[i].lng);
                            if (d < bestDist) {
                                best = areas[i].name;
                                bestDist = d;
                            }
                        }
                        self.areaName = best || '{{ __("nav.location") }}';
                        localStorage.setItem('nite_area', self.areaName);
                        self.loading = false;
                    },
                    function() { self.loading = false; },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 }
                );
            },
            init: function() {
                // 이전에 위치 허용했으면 자동 갱신
                var self = this;
                if (navigator.permissions) {
                    navigator.permissions.query({ name: 'geolocation' }).then(function(r) {
                        if (r.state === 'granted') self.refresh();
                    }).catch(function(){});
                }
            }
        };
    }
    </script>

    {{-- 번역 로딩 오버레이 --}}
    <div id="lang-overlay" style="display:none" class="fixed inset-0 z-[9999] flex items-center justify-center bg-dark-950/95 backdrop-blur-sm">
        <div class="text-center">
            <div class="relative w-16 h-16 mx-auto mb-5">
                <div class="absolute inset-0 rounded-full border-[3px] border-accent/20"></div>
                <div class="absolute inset-0 rounded-full border-[3px] border-transparent border-t-accent animate-spin"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5a17.92 17.92 0 01-8.716-2.247m0 0A8.966 8.966 0 013 12c0-1.97.633-3.794 1.706-5.282"/></svg>
                </div>
            </div>
            <p id="lang-overlay-text" class="text-[16px] font-bold text-white mb-2"></p>
            <p id="lang-overlay-sub" class="text-[12px] text-gray-500"></p>
        </div>
    </div>
    <script>
    (function() {
        const msgs = {
            ko: { text: '번역 중...', sub: '잠시만 기다려주세요' },
            en: { text: 'Translating...', sub: 'Please wait a moment' },
            ja: { text: '翻訳中...', sub: 'しばらくお待ちください' },
            zh: { text: '翻译中...', sub: '请稍候' },
        };
        window.addEventListener('lang-switch', function(e) {
            var code = e.detail.code, url = e.detail.url;
            var overlay = document.getElementById('lang-overlay');
            var msg = msgs[code] || msgs['en'];
            document.getElementById('lang-overlay-text').textContent = msg.text;
            document.getElementById('lang-overlay-sub').textContent = msg.sub;
            overlay.style.display = 'flex';
            setTimeout(function() { window.location.href = url; }, 150);
        });
        // 페이지 로드 완료 시 오버레이 숨김 (뒤로가기 대응)
        window.addEventListener('pageshow', function() {
            document.getElementById('lang-overlay').style.display = 'none';
        });
    })();
    </script>

    {{-- 기기 식별자 및 시간대 수집 --}}
    <script>
    (function() {
        // guestId 생성/유지 (localStorage)
        let guestId = localStorage.getItem('nite_guest_id');
        if (!guestId) {
            guestId = 'g_' + Date.now().toString(36) + '_' + Math.random().toString(36).substr(2, 8);
            localStorage.setItem('nite_guest_id', guestId);
        }
        document.cookie = 'nite_guest_id=' + guestId + ';path=/;max-age=31536000;SameSite=Lax';

        // deviceId (앱에서 JavascriptInterface로 가져오거나 웹에서 생성)
        let deviceId = null;
        const isNiteApp = window.NiteApp && typeof window.NiteApp.getDeviceId === 'function';
        if (isNiteApp) {
            deviceId = window.NiteApp.getDeviceId();
            localStorage.setItem('nite_device_id', deviceId);
        } else {
            deviceId = localStorage.getItem('nite_device_id');
            if (!deviceId) {
                deviceId = 'web_' + Date.now().toString(36) + '_' + Math.random().toString(36).substr(2, 8);
                localStorage.setItem('nite_device_id', deviceId);
            }
        }
        document.cookie = 'nite_device_id=' + deviceId + ';path=/;max-age=31536000;SameSite=Lax';
    })();
    </script>

    <script>
    function reviewComposer(sectionKey) {
        return {
            submitting: false,
            success: '',
            error: '',
            successTimeoutId: null,
            parseError(payload, fallbackMessage) {
                const detailMessage = payload?.errors
                    ? Object.values(payload.errors)[0]?.[0]
                    : null;

                return detailMessage || payload?.message || fallbackMessage;
            },
            flashSuccess(message) {
                if (this.successTimeoutId) {
                    clearTimeout(this.successTimeoutId);
                }

                this.success = message;
                this.successTimeoutId = window.setTimeout(() => {
                    this.success = '';
                    this.successTimeoutId = null;
                }, 3000);
            },
            async refreshReviewSection() {
                if (!this.$refs.content) {
                    return false;
                }

                try {
                    const response = await fetch(window.location.href, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html',
                        },
                        cache: 'no-store',
                    });

                    if (!response.ok) {
                        return false;
                    }

                    const html = await response.text();
                    const documentFragment = new DOMParser().parseFromString(html, 'text/html');
                    const nextSection = documentFragment.querySelector(`[data-review-section="${sectionKey}"] [data-review-content]`);

                    if (!nextSection) {
                        return false;
                    }

                    this.$refs.content.innerHTML = nextSection.innerHTML;

                    if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                        window.Alpine.initTree(this.$refs.content);
                    }

                    return true;
                } catch (_) {
                    return false;
                }
            },
            async submit(event) {
                const form = event.target;
                const ratingField = form.querySelector('[name="rating"]');

                if (!ratingField || !ratingField.value) {
                    window.alert('평점을 선택해 주세요.');
                    ratingField?.focus();
                    return;
                }

                if (!form.reportValidity()) {
                    return;
                }

                const formData = new FormData(form);

                this.submitting = true;
                this.success = '';
                this.error = '';

                try {
                    const response = await fetch(form.action, {
                        method: form.method || 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });

                    const payload = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        throw new Error(this.parseError(payload, '후기 등록에 실패했습니다.'));
                    }

                    const refreshed = await this.refreshReviewSection();
                    this.flashSuccess(payload?.message || '후기가 등록되었습니다.');

                    if (!refreshed) {
                        window.location.reload();
                    }
                } catch (error) {
                    this.error = error.message || '후기 등록 중 오류가 발생했습니다.';
                } finally {
                    this.submitting = false;
                }
            },
        };
    }
    </script>

    @include('layouts.partials.event-tracking')
    @stack('scripts')
</body>
</html>
