@extends('layouts.app')
@section('title', '내 주변 스팟')

@section('content')
<div class="space-y-6 pb-4" x-data="nearbyPage()" x-init="init()">

    {{-- ═══ 상단 헤더 ═══ --}}
    <section class="mx-4 mt-5">
        <div class="relative rounded-3xl overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-cyan-900/30 via-dark-900 to-blue-900/20"></div>
            <div class="relative z-10 px-6 py-6">
                <div class="flex items-center gap-2 mb-3">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"
                              :class="hasGps ? 'bg-green-400' : 'bg-yellow-400'"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2"
                              :class="hasGps ? 'bg-green-400' : 'bg-yellow-400'"></span>
                    </span>
                    <span class="text-[11px] font-semibold tracking-wide uppercase"
                          :class="hasGps ? 'text-green-400' : 'text-yellow-400'"
                          x-text="hasGps ? 'GPS 활성' : (locationDenied ? '위치 수동선택' : '위치 확인 필요')"></span>
                    <template x-if="nearestArea">
                        <span class="text-[11px] text-gray-500" x-text="'· ' + nearestArea + ' 근처'"></span>
                    </template>
                </div>
                <h1 class="text-[22px] font-extrabold leading-tight tracking-tight mb-1">
                    <span class="gradient-text">내 주변 스팟</span>
                </h1>
                <p class="text-[13px] text-gray-400 leading-relaxed">지금 위치에서 바로 이어가기 좋은 스팟을 거리 순으로 보여드립니다.</p>

                {{-- 상태 카운트 --}}
                @if($status)
                <div class="flex gap-4 mt-4">
                    <div class="text-center">
                        <p class="text-[18px] font-extrabold text-cyan-400">{{ $status['nearbyClubCount'] }}</p>
                        <p class="text-[10px] text-gray-500">근처 클럽</p>
                    </div>
                    <div class="text-center">
                        <p class="text-[18px] font-extrabold text-green-400">{{ $status['openClubCount'] }}</p>
                        <p class="text-[10px] text-gray-500">영업 중</p>
                    </div>
                    <div class="text-center">
                        <p class="text-[18px] font-extrabold text-pink-400">{{ $status['nearbyPartyCount'] }}</p>
                        <p class="text-[10px] text-gray-500">오늘 파티</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </section>

    {{-- ═══ 위치 제어 ═══ --}}
    <section class="px-4">
        {{-- GPS 위치 사용 버튼 --}}
        <div class="flex gap-2 mb-3">
            <button @click="requestLocation()"
                :disabled="loading"
                class="flex-1 flex items-center justify-center gap-2 py-3 rounded-2xl text-[12px] font-semibold transition-all"
                :class="hasGps ? 'bg-cyan-500/15 text-cyan-400 border border-cyan-500/20' : 'btn-primary text-white'">
                <svg class="w-4 h-4" :class="loading ? 'animate-spin' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <template x-if="!loading">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                    </template>
                    <template x-if="loading">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                    </template>
                </svg>
                <span x-text="hasGps ? '위치 다시 가져오기' : (loading ? '위치 확인 중...' : '현재 위치 사용하기')"></span>
            </button>
        </div>

        {{-- 위치 거부 또는 보조: 지역 선택 --}}
        <div class="flex gap-2 overflow-x-auto scrollbar-hide -mx-4 px-4 pb-2">
            @foreach($areas as $a)
                @php
                    $coords = $areaCenters[$a] ?? null;
                    $isSelected = $selectedArea === $a;
                @endphp
                <a href="{{ route('nearby.index', array_filter([
                        'area' => $a,
                        'lat' => $coords['lat'] ?? '',
                        'lng' => $coords['lng'] ?? '',
                        'genre' => $filters['genre'],
                        'budget' => $filters['budget'],
                    ])) }}"
                    class="shrink-0 px-3.5 py-2 rounded-full text-[12px] font-semibold transition-all
                    {{ $isSelected ? 'bg-cyan-500 text-white' : 'bg-dark-700 text-gray-400 border border-white/[0.06]' }}">
                    {{ $a }}
                </a>
            @endforeach
        </div>

        {{-- 장르 + 예산 필터 --}}
        @if($hasLocation)
        <form method="GET" action="{{ route('nearby.index') }}" class="flex gap-2 mt-1">
            <input type="hidden" name="lat" value="{{ $userLat }}">
            <input type="hidden" name="lng" value="{{ $userLng }}">
            @if($selectedArea)
                <input type="hidden" name="area" value="{{ $selectedArea }}">
            @endif
            <select name="genre" onchange="this.form.submit()"
                class="flex-1 bg-dark-700 border border-white/[0.06] rounded-xl px-3 py-2.5 text-[12px] text-gray-300 appearance-none">
                <option value="">모든 장르</option>
                @foreach($genres as $g)
                    <option value="{{ $g }}" {{ ($filters['genre'] ?? '') === $g ? 'selected' : '' }}>{{ $g }}</option>
                @endforeach
            </select>
            <select name="budget" onchange="this.form.submit()"
                class="flex-1 bg-dark-700 border border-white/[0.06] rounded-xl px-3 py-2.5 text-[12px] text-gray-300 appearance-none">
                <option value="">예산 무관</option>
                <option value="20000" {{ ($filters['budget'] ?? '') == '20000' ? 'selected' : '' }}>2만원 이하</option>
                <option value="30000" {{ ($filters['budget'] ?? '') == '30000' ? 'selected' : '' }}>3만원 이하</option>
                <option value="50000" {{ ($filters['budget'] ?? '') == '50000' ? 'selected' : '' }}>5만원 이하</option>
                <option value="100000" {{ ($filters['budget'] ?? '') == '100000' ? 'selected' : '' }}>10만원 이하</option>
            </select>
        </form>
        @endif
    </section>

    {{-- ═══ 위치 미확인 시 안내 ═══ --}}
    @if(!$hasLocation)
    <section class="px-4">
        <div class="bg-dark-800/50 rounded-3xl p-8 text-center border border-white/[0.03]">
            <div class="w-16 h-16 mx-auto mb-4 rounded-3xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center">
                <svg class="w-8 h-8 text-cyan-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                </svg>
            </div>
            <h3 class="text-[16px] font-bold text-gray-200 mb-2">위치를 확인해주세요</h3>
            <p class="text-[13px] text-gray-500 mb-1 leading-relaxed">
                현재 위치를 사용하면<br>가까운 클럽과 파티를 추천받을 수 있어요
            </p>
            <p class="text-[11px] text-gray-600 mb-5">또는 위의 지역 버튼을 선택해 주세요</p>

            <button @click="requestLocation()"
                class="btn-primary inline-flex items-center gap-2 px-6 py-3 rounded-2xl text-[13px] font-bold text-white">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                </svg>
                현재 위치 사용하기
            </button>

            <div class="mt-4 flex items-center gap-2 justify-center text-[11px] text-gray-600">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                위치 정보는 추천에만 사용되며 저장되지 않습니다
            </div>
        </div>
    </section>
    @endif

    {{-- ═══ 추천 결과 ═══ --}}
    @if($hasLocation && $result)
        @if(!empty($result['sections']))
            @foreach($result['sections'] as $sIdx => $section)
                @if(!empty($section['items']))
                <section class="px-4 animate-fade-up" style="animation-delay: {{ $sIdx * 80 }}ms">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h2 class="text-[16px] font-bold flex items-center gap-1.5 tracking-tight">
                                <span>{{ $section['emoji'] }}</span>
                                {{ $section['title'] }}
                            </h2>
                            @if(!empty($section['desc']))
                                <p class="text-[11px] text-gray-500 mt-0.5">{{ $section['desc'] }}</p>
                            @endif
                        </div>
                        <span class="text-[11px] text-gray-600 bg-dark-700 px-2.5 py-1 rounded-full">{{ count($section['items']) }}건</span>
                    </div>
                    <div class="space-y-2.5">
                        @foreach($section['items'] as $item)
                            <x-nearby-card :item="$item" />
                        @endforeach
                    </div>
                </section>
                @endif
            @endforeach

            {{-- 오늘밤 추천 연결 --}}
            <section class="px-4">
                <div class="flex gap-2">
                    <a href="{{ route('tonight.index') }}"
                       class="flex-1 flex items-center justify-center gap-2 py-3 rounded-2xl bg-dark-700 border border-white/[0.06] text-[12px] font-semibold text-gray-300 active:scale-[0.98] transition-transform">
                        <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                        오늘밤 추천
                    </a>
                    <a href="{{ route('tour.index') }}"
                       class="flex-1 flex items-center justify-center gap-2 py-3 rounded-2xl bg-dark-700 border border-white/[0.06] text-[12px] font-semibold text-gray-300 active:scale-[0.98] transition-transform">
                        <svg class="w-4 h-4 text-pink-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z"/></svg>
                        AI 투어 추천
                    </a>
                </div>
            </section>

        @elseif($result['fallback'])
            {{-- 결과 없음 --}}
            <section class="px-4">
                <x-empty-state icon="📍" message="근처에 추천할 곳이 없습니다">
                    <div class="mt-6 space-y-2.5">
                        @foreach($result['fallback'] as $fb)
                            @if(isset($fb['link']))
                                <a href="{{ $fb['link'] }}"
                                   class="flex items-center gap-3 p-3.5 bg-dark-700/50 rounded-2xl border border-white/[0.04] hover:border-cyan-500/20 transition-colors">
                                    <span class="text-lg">{{ $fb['icon'] ?? '💡' }}</span>
                                    <span class="text-[13px] text-gray-300 font-medium flex-1">{{ $fb['text'] }}</span>
                                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                                </a>
                            @elseif(($fb['action'] ?? '') === 'expand_radius')
                                <a href="{{ route('nearby.index', array_filter([
                                        'lat' => $userLat, 'lng' => $userLng,
                                        'radius' => $fb['params']['radius'] ?? 10,
                                        'genre' => $filters['genre'], 'budget' => $filters['budget'],
                                    ])) }}"
                                   class="flex items-center gap-3 p-3.5 bg-dark-700/50 rounded-2xl border border-white/[0.04] hover:border-cyan-500/20 transition-colors">
                                    <span class="text-lg">{{ $fb['icon'] ?? '📍' }}</span>
                                    <span class="text-[13px] text-gray-300 font-medium flex-1">{{ $fb['text'] }}</span>
                                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                                </a>
                            @endif
                        @endforeach
                    </div>
                </x-empty-state>
            </section>
        @endif
    @endif
</div>

@push('scripts')
<script>
function nearbyPage() {
    return {
        hasGps: {{ $hasLocation ? 'true' : 'false' }},
        loading: false,
        locationDenied: false,
        nearestArea: {!! json_encode($result['nearestArea'] ?? ($selectedArea ?? null)) !!},

        init() {
            // URL에 좌표가 없고 세션에 저장된 위치도 없으면 자동 요청하지 않음
            // 사용자가 명시적으로 버튼 클릭 시에만 요청
        },

        requestLocation() {
            if (!navigator.geolocation) {
                this.locationDenied = true;
                return;
            }

            this.loading = true;

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;

                    // 위치 저장 (세션)
                    fetch('/me/location', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ lat, lng }),
                    }).catch(() => {});

                    // 페이지 리로드 with 좌표
                    const params = new URLSearchParams(window.location.search);
                    params.set('lat', lat.toFixed(6));
                    params.set('lng', lng.toFixed(6));
                    params.delete('area'); // GPS 사용 시 지역 제거
                    window.location.href = '/nearby?' + params.toString();
                },
                (err) => {
                    this.loading = false;
                    this.locationDenied = true;
                    console.warn('위치 권한 거부:', err.message);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 300000, // 5분 캐시
                }
            );
        },
    };
}
</script>
@endpush
@endsection
