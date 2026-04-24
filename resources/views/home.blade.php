@extends('layouts.app')
@section('title', '오늘 밤, 어디로 갈까')
@section('page_type', 'home')

@section('content')
<div class="space-y-7 pb-4">

    {{-- ═══ Hero ═══ --}}
    <section class="relative mx-4 mt-5 rounded-3xl overflow-hidden">
        <div class="absolute inset-0">
            <img src="{{ asset('images/hero.jpg') }}" class="w-full h-full object-cover" alt="">
            <div class="absolute inset-0 bg-gradient-to-t from-dark-950 via-dark-950/85 to-dark-950/35"></div>
            <div class="absolute inset-0 bg-gradient-to-br from-cyan-500/10 via-transparent to-pink-500/15"></div>
        </div>
        <div class="relative z-10 px-5 pt-7 pb-5">
            <div class="flex items-center gap-2 mb-3">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-green-400"></span>
                </span>
                <span class="text-[11px] text-green-400 font-semibold tracking-wide uppercase">{{ trans_auto($tonightStatus['timeSlot']['label']) }}</span>
                <span class="text-[11px] text-gray-500">· {{ now()->format('n.j') }} {{ trans_auto($tonightStatus['dayOfWeek']) }}</span>
            </div>
            <h1 class="text-[28px] font-extrabold leading-tight tracking-tight mb-2">
                {!! nl2br(e(trans_auto("오늘 밤,\n무드에 맞게 골라보세요"))) !!}
            </h1>
            <p class="text-[13px] text-gray-300 leading-relaxed">
                {{ trans_auto($tonightSummary['personalSummary'] ?? '클럽, 파티, 투어를 한 흐름으로 바로 이어보세요.') }}
            </p>

            <div class="mt-5">
                <a href="{{ route('tonight.index') }}"
                   class="btn-primary inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-2xl px-5 py-3 text-[13px] font-bold text-white"
                   data-track-event="home_primary_cta_click"
                   data-track-context="hero_primary"
                   data-track-label="지금 뜨는 밤 찾기">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                    {{ trans_auto('지금 뜨는 밤 찾기') }}
                </a>
            </div>

            <a href="{{ $heroSecondary['url'] }}"
               class="mt-3 flex items-center justify-between rounded-2xl border border-white/10 bg-black/20 px-4 py-3 backdrop-blur"
               data-track-event="home_quick_filter_click"
               data-track-context="hero_secondary"
               data-track-label="{{ $heroSecondary['title'] }}">
                <div class="min-w-0">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-gray-500">{{ trans_auto($heroSecondary['eyebrow']) }}</p>
                    <p class="mt-1 truncate text-[13px] font-semibold text-white">{{ trans_auto($heroSecondary['title']) }}</p>
                    <p class="mt-1 truncate text-[11px] text-gray-400">{{ trans_auto($heroSecondary['meta']) }}</p>
                </div>
                <svg class="ml-3 h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            </a>

            <div class="mt-4 grid grid-cols-3 gap-2">
                @foreach($heroHighlights as $highlight)
                    @php
                        $toneClasses = match ($highlight['tone']) {
                            'pink' => 'border-pink-500/20 bg-pink-500/10 text-pink-200',
                            'emerald' => 'border-emerald-500/20 bg-emerald-500/10 text-emerald-200',
                            default => 'border-sky-500/20 bg-sky-500/10 text-sky-200',
                        };
                    @endphp
                    <a href="{{ $highlight['url'] }}"
                       class="rounded-2xl border px-3 py-3 backdrop-blur {{ $toneClasses }}"
                       data-track-event="home_quick_filter_click"
                       data-track-context="hero_highlight"
                       data-track-label="{{ $highlight['label'] }}">
                        <p class="text-[10px] font-medium text-white/75">{{ trans_auto($highlight['label']) }}</p>
                        <p class="mt-1 text-[18px] font-extrabold leading-none">{{ $highlight['value'] }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    @include('partials.revisit-hub', [
        'hub' => $revisitHub,
        'surface' => 'home',
        'title' => '다시 이어가기',
        'subtitle' => '끊긴 동선을 바로 이어서, 오늘 밤 분위기를 다시 이어갑니다.',
    ])

    {{-- ═══ 오늘밤 추천 섹션 ═══ --}}
    @if(!empty($tonightSummary['items']) && $tonightSummary['items']->isNotEmpty())
    <section class="px-4 pt-1">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-[17px] font-bold flex items-center gap-2 tracking-tight">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-accent opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-accent"></span>
                    </span>
                    <span>✨</span>
                    {{ __('home.tonight') }}
                </h2>
                <p class="text-[11px] text-gray-500 mt-0.5 ml-6">{{ trans_auto($tonightSummary['personalSummary']) }}</p>
            </div>
            <a href="{{ route('tonight.index') }}" class="text-[11px] text-gray-500 font-medium flex items-center gap-0.5 active:text-accent transition-colors">
                {{ __('common.view_all') }}
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            </a>
        </div>

        {{-- 추천 카드 캐러셀 --}}
        <div class="flex gap-3 overflow-x-auto scrollbar-hide -mx-4 px-4 pb-1 snap-x">
            @foreach($tonightSummary['items'] as $item)
                <x-tonight-card :item="$item" :compact="true" />
            @endforeach
        </div>

        {{-- 빠른 추천 CTA --}}
        <div class="mt-3 flex gap-2">
            <a href="{{ route('tonight.index') }}"
               class="flex-1 flex items-center justify-center gap-2 py-3 rounded-2xl bg-dark-700 border border-white/[0.06] text-[12px] font-semibold text-gray-300 active:scale-[0.98] transition-transform">
                <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                {{ __('home.tonight_more') }}
            </a>
            <a href="{{ route('tour.index') }}"
               class="flex-1 flex items-center justify-center gap-2 py-3 rounded-2xl bg-dark-700 border border-white/[0.06] text-[12px] font-semibold text-gray-300 active:scale-[0.98] transition-transform">
                <svg class="w-4 h-4 text-pink-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z"/></svg>
                {{ __('home.ai_tour') }}
            </a>
        </div>
    </section>
    @endif

    {{-- ═══ 배너 ═══ --}}
    @if($banners->isNotEmpty())
    <section class="px-4 pt-1" x-data="{ current: 0, total: {{ $banners->count() }} }" x-init="setInterval(() => current = (current + 1) % total, 4000)">
        <div class="relative rounded-2xl overflow-hidden h-36">
            @foreach($banners as $i => $banner)
            <a href="{{ $banner->link_url ?? '#' }}"
               x-show="current === {{ $i }}"
               x-transition:enter="transition ease-out duration-500"
               x-transition:enter-start="opacity-0"
               x-transition:enter-end="opacity-100"
               x-transition:leave="transition ease-in duration-300"
               x-transition:leave-start="opacity-100"
               x-transition:leave-end="opacity-0"
               class="absolute inset-0 block">
                <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" class="w-full h-full object-cover">
                <div class="absolute bottom-0 left-0 right-0 px-4 py-3 bg-gradient-to-t from-black/70 to-transparent">
                    <p class="text-[13px] font-bold text-white">{{ $banner->title }}</p>
                </div>
            </a>
            @endforeach
            @if($banners->count() > 1)
            <div class="absolute bottom-3 right-4 flex gap-1.5">
                @foreach($banners as $i => $b)
                <button @click="current = {{ $i }}" class="w-1.5 h-1.5 rounded-full transition-all" :class="current === {{ $i }} ? 'bg-white w-4' : 'bg-white/40'"></button>
                @endforeach
            </div>
            @endif
        </div>
    </section>
    @endif

    {{-- ═══ 맞춤 추천 (관심 설정 있을 때) ═══ --}}
    @if($hasPreferences && $personalizedParties->isNotEmpty())
    <section class="px-4 pt-1">
        <x-section-header :title="__('home.personalized_party')" badge="✨" :action="route('parties.index')" />
        <div class="flex gap-3 overflow-x-auto scrollbar-hide -mx-4 px-4 pb-1 snap-x">
            @foreach($personalizedParties as $party)
                <x-party-card :party="$party" layout="carousel" />
            @endforeach
        </div>
    </section>
    @endif

    {{-- ═══ 오늘의 파티 ═══ --}}
    <section class="px-4 pt-1">
        <x-section-header :title="__('home.today_party')" :pulse="true"
            :action="route('parties.index', ['date' => today()->toDateString()])" />

        @if($todayParties->isEmpty())
            <x-empty-state :message="__('home.no_party')"
                :action="route('parties.index')" :actionLabel="__('home.other_date')" />
        @else
            <div class="flex gap-3 overflow-x-auto scrollbar-hide -mx-4 px-4 pb-1 snap-x">
                @foreach($todayParties as $party)
                    <x-party-card :party="$party" layout="carousel" />
                @endforeach
            </div>
        @endif
    </section>

    {{-- ═══ 맞춤 클럽 (관심 설정 있을 때) 또는 핫한 클럽 ═══ --}}
    <section class="px-4 pt-1">
        @if($hasPreferences && $personalizedClubs->isNotEmpty())
            <x-section-header :title="__('home.personalized_club')" badge="✨" :action="route('clubs.index')" />
            <div class="grid grid-cols-2 gap-2.5">
                @foreach($personalizedClubs as $club)
                    <x-club-card :club="$club" layout="grid" />
                @endforeach
            </div>
            <p class="text-center text-[10px] text-gray-600 mt-2">{{ __('home.pref_based') }} · <a href="{{ route('my.preferences') }}" class="text-accent">{{ __('home.pref_change') }}</a></p>
        @else
            <x-section-header :title="__('home.hot_club')" badge="🔥" :action="route('clubs.index')" />
            <div class="grid grid-cols-2 gap-2.5">
                @foreach($hotClubs as $club)
                    <x-club-card :club="$club" layout="grid" />
                @endforeach
            </div>
        @endif
    </section>

    {{-- ═══ 실시간 후기 ═══ --}}
    <section class="px-4 pt-1">
        <x-section-header :title="__('home.realtime')" badge="💬" :action="route('community.index')" />
        <div class="space-y-2.5">
            @foreach($recentPosts as $post)
                <x-post-card :post="$post" :compact="true" />
            @endforeach
        </div>
    </section>

    {{-- ═══ 내 근처 추천 (Near Me) ═══ --}}
    <section class="px-4 pt-1" x-data="homeNearby()" x-init="tryAutoLoad()">
        <template x-if="loaded && items.length > 0">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-[17px] font-bold flex items-center gap-2 tracking-tight">
                            <span class="text-cyan-400">📍</span>
                            {{ __('home.nearby') }}
                        </h2>
                        <p class="text-[11px] text-gray-500 mt-0.5 ml-6" x-text="nearestArea + ' {{ __('home.nearby_found_near') }} · ' + total + ' {{ __('home.nearby_found_count') }}'"></p>
                    </div>
                    <a :href="'/nearby?lat=' + lat + '&lng=' + lng" class="text-[11px] text-gray-500 font-medium flex items-center gap-0.5 active:text-accent transition-colors">
                        {{ __('common.view_all') }}
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                    </a>
                </div>
                <div class="flex gap-3 overflow-x-auto scrollbar-hide -mx-4 px-4 pb-1 snap-x" x-ref="nearbyCarousel">
                </div>
            </div>
        </template>

        {{-- 위치 미확인 → CTA 카드 --}}
        <template x-if="!loaded && !loading">
            <a href="{{ route('nearby.index') }}" class="card p-4 flex items-center gap-4 group">
                <div class="w-12 h-12 rounded-2xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center shrink-0 group-active:scale-90 transition-transform">
                    <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[13px] font-bold text-gray-200">{{ __('home.nearby_cta') }}</p>
                    <p class="text-[11px] text-gray-500 mt-0.5">{{ __('home.nearby_desc') }}</p>
                </div>
                <svg class="w-5 h-5 text-gray-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            </a>
        </template>
    </section>

    {{-- ═══ Quick Actions ═══ --}}
    <section class="px-4 pt-1 grid grid-cols-3 gap-2.5">
        <a href="{{ route('nearby.index') }}" class="card p-4 text-center group" data-track-event="home_quick_filter_click" data-track-context="quick_action" data-track-label="Near Me">
            <div class="w-10 h-10 mx-auto mb-2 rounded-2xl bg-cyan-500/10 flex items-center justify-center text-xl group-active:scale-90 transition-transform">📍</div>
            <span class="text-[12px] font-semibold text-gray-300">{{ __('home.near_me') }}</span>
            <p class="text-[10px] text-gray-600 mt-0.5">가까운 곳</p>
        </a>
        <a href="{{ route('clubs.index', ['foreigner' => 1]) }}" class="card p-4 text-center group" data-track-event="home_quick_filter_click" data-track-context="quick_action" data-track-label="외국인 OK">
            <div class="w-10 h-10 mx-auto mb-2 rounded-2xl bg-blue-500/10 flex items-center justify-center text-xl group-active:scale-90 transition-transform">🌍</div>
            <span class="text-[12px] font-semibold text-gray-300">{{ __('club.foreigner_ok') }}</span>
            <p class="text-[10px] text-gray-600 mt-0.5">누구나 OK</p>
        </a>
        <a href="{{ route('tonight.index') }}" class="card p-4 text-center group" data-track-event="home_quick_filter_click" data-track-context="quick_action" data-track-label="Tonight">
            <div class="w-10 h-10 mx-auto mb-2 rounded-2xl bg-pink-500/10 flex items-center justify-center text-xl group-active:scale-90 transition-transform">🔥</div>
            <span class="text-[12px] font-semibold text-gray-300">{{ __('home.tonight_btn') }}</span>
            <p class="text-[10px] text-gray-600 mt-0.5">지금 추천</p>
        </a>
    </section>

</div>

@push('scripts')
<script>
function homeNearby() {
    return {
        loaded: false,
        loading: false,
        items: [],
        nearestArea: '',
        total: 0,
        lat: 0,
        lng: 0,

        tryAutoLoad() {
            // 이전에 위치를 허용한 적이 있다면 자동으로 가져옴
            if (navigator.permissions) {
                navigator.permissions.query({ name: 'geolocation' }).then(result => {
                    if (result.state === 'granted') {
                        this.fetchLocation();
                    }
                }).catch(() => {});
            }
        },

        fetchLocation() {
            if (!navigator.geolocation) return;
            this.loading = true;

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    this.lat = pos.coords.latitude;
                    this.lng = pos.coords.longitude;
                    this.loadNearby();
                },
                () => { this.loading = false; },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 }
            );
        },

        loadNearby() {
            fetch('/nearby/summary?lat=' + this.lat + '&lng=' + this.lng, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                this.loading = false;
                if (data.summary && data.summary.items) {
                    this.items = data.summary.items;
                    this.nearestArea = data.summary.nearestArea || '';
                    this.total = data.summary.total || 0;
                    this.loaded = this.items.length > 0;
                    if (this.loaded) {
                        this.$nextTick(() => this.renderCards());
                    }
                }
            })
            .catch(() => { this.loading = false; });
        },

        renderCards() {
            const el = this.$refs.nearbyCarousel;
            if (!el) return;
            let html = '';
            this.items.forEach(item => {
                const entity = item.entity;
                const isParty = item.type === 'party';
                const name = entity.name;
                const thumb = entity.thumbnail || entity.thumbnail_url || 'https://images.unsplash.com/photo-1571266028243-e4733b0f0bb0?w=400&q=80';
                const labels = (item.labels || []).slice(0, 2);
                const distText = item.distance_text || '';
                const travelText = item.travel_text || '';
                const costText = item.cost_text || '';
                const link = item.link || '#';

                let labelsHtml = labels.map(l => {
                    const colorMap = {
                        green: 'background:rgba(34,197,94,0.15);color:#4ade80;border-color:rgba(34,197,94,0.2)',
                        cyan: 'background:rgba(6,182,212,0.15);color:#22d3ee;border-color:rgba(6,182,212,0.2)',
                        orange: 'background:rgba(249,115,22,0.15);color:#fb923c;border-color:rgba(249,115,22,0.2)',
                        yellow: 'background:rgba(234,179,8,0.15);color:#facc15;border-color:rgba(234,179,8,0.2)',
                        red: 'background:rgba(239,68,68,0.15);color:#f87171;border-color:rgba(239,68,68,0.2)',
                        blue: 'background:rgba(59,130,246,0.15);color:#60a5fa;border-color:rgba(59,130,246,0.2)',
                    };
                    return `<span style="font-size:8px;font-weight:700;padding:2px 6px;border-radius:9999px;border:1px solid;${colorMap[l.color]||''}">${l.text}</span>`;
                }).join('');

                html += `<a href="${link}" class="snap-start shrink-0 w-[200px] card overflow-hidden group">
                    <div class="relative h-28 img-overlay">
                        <img src="${thumb}" alt="${name}" class="w-full h-full object-cover" loading="lazy">
                        <div style="position:absolute;top:8px;left:8px;z-index:10;display:flex;flex-wrap:wrap;gap:4px">${labelsHtml}</div>
                    </div>
                    <div class="p-3">
                        <p style="font-size:12px;font-weight:700" class="text-gray-100 truncate">${name}</p>
                        <div style="display:flex;align-items:center;gap:6px;margin-top:4px">
                            ${distText ? `<span style="font-size:10px;font-weight:600" class="text-cyan-400">${distText}</span>` : ''}
                            ${travelText ? `<span style="font-size:10px" class="text-gray-500">${travelText}</span>` : ''}
                        </div>
                        <div style="display:flex;justify-content:space-between;margin-top:8px;padding-top:8px;border-top:1px solid rgba(255,255,255,0.04)">
                            <span style="font-size:11px;font-weight:700" class="text-accent">${isParty ? '{{ __("nav.party") }}' : '{{ __("nav.club") }}'}</span>
                            ${costText ? `<span style="font-size:10px" class="text-gray-400">${costText}</span>` : ''}
                        </div>
                    </div>
                </a>`;
            });
            el.innerHTML = html;
        },
    };
}
</script>
@endpush
@endsection
