@extends('layouts.app')
@section('title', '오늘의 픽')

@section('content')
<div class="space-y-6 pb-4" x-data="tonightPage()">

    {{-- ═══ 상단 헤더: 시간대 상태 ═══ --}}
    <section class="mx-4 mt-5">
        <div class="relative rounded-3xl overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-purple-900/40 via-dark-900 to-pink-900/30"></div>
            <div class="relative z-10 px-6 py-6">
                {{-- 현재 상태 --}}
                <div class="flex items-center gap-2 mb-3">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-400"></span>
                    </span>
                    <span class="text-[11px] text-green-400 font-semibold tracking-wide uppercase">{{ $result['timeSlot']['label'] }}</span>
                    <span class="text-[11px] text-gray-500">· {{ now()->format('H:i') }}</span>
                </div>

                <h1 class="text-[22px] font-extrabold leading-tight tracking-tight mb-1">
                    <span class="gradient-text">오늘의 픽</span>
                </h1>
                <p class="text-[13px] text-gray-400 leading-relaxed">지금 시간대와 분위기에 맞는 스팟을 빠르게 골라드립니다.</p>

                {{-- 실시간 카운트 --}}
                @if(isset($status))
                <div class="flex gap-4 mt-4">
                    <div class="text-center">
                        <p class="text-[18px] font-extrabold text-white">{{ $status['todayPartyCount'] }}</p>
                        <p class="text-[10px] text-gray-500">오늘 파티</p>
                    </div>
                    <div class="text-center">
                        <p class="text-[18px] font-extrabold text-green-400">{{ $status['openClubCount'] }}</p>
                        <p class="text-[10px] text-gray-500">영업 중 클럽</p>
                    </div>
                    @if($status['upcomingSoonCount'] > 0)
                    <div class="text-center">
                        <p class="text-[18px] font-extrabold text-orange-400">{{ $status['upcomingSoonCount'] }}</p>
                        <p class="text-[10px] text-gray-500">곧 시작</p>
                    </div>
                    @endif
                    @if($status['isWeekend'])
                    <div class="text-center">
                        <p class="text-[18px] font-extrabold text-pink-400">{{ $status['dayOfWeek'] }}</p>
                        <p class="text-[10px] text-gray-500">주말</p>
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </section>

    {{-- ═══ 빠른 필터 ═══ --}}
    <section class="px-4" x-data="tonightFilters()">
        {{-- 지역 선택 (각 버튼이 독립 링크) --}}
        <div class="flex gap-2 overflow-x-auto scrollbar-hide -mx-4 px-4 pb-2">
            <a href="{{ route('tonight.index', array_filter(['genre' => $filters['genre'], 'budget' => $filters['budget']])) }}"
                class="shrink-0 px-3.5 py-2 rounded-full text-[12px] font-semibold transition-all
                {{ !$filters['area'] ? 'bg-accent text-white' : 'bg-dark-700 text-gray-400 border border-white/[0.06]' }}">
                전체 지역
            </a>
            @foreach($areas as $a)
                <a href="{{ route('tonight.index', array_filter(['area' => $a, 'genre' => $filters['genre'], 'budget' => $filters['budget']])) }}"
                    class="shrink-0 px-3.5 py-2 rounded-full text-[12px] font-semibold transition-all
                    {{ $filters['area'] === $a ? 'bg-accent text-white' : 'bg-dark-700 text-gray-400 border border-white/[0.06]' }}">
                    {{ $a }}
                </a>
            @endforeach
        </div>
        {{-- 장르 + 예산 --}}
        <form method="GET" action="{{ route('tonight.index') }}" class="flex gap-2 mt-1">
            @if($filters['area'])
                <input type="hidden" name="area" value="{{ $filters['area'] }}">
            @endif
            <select name="genre" onchange="this.form.submit()"
                class="flex-1 bg-dark-700 border border-white/[0.06] rounded-xl px-3 py-2.5 text-[12px] text-gray-300 appearance-none">
                <option value="">모든 장르</option>
                @foreach($genres as $g)
                    <option value="{{ $g }}" {{ $filters['genre'] === $g ? 'selected' : '' }}>{{ $g }}</option>
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
        <div class="mt-3 flex flex-wrap items-center gap-2 text-[11px] text-gray-400">
            <x-badge variant="green" size="xs" pill>{{ trans_auto('실이벤트') }}</x-badge>
            <span>{{ trans_auto('공식 일정·외부 공지 확인') }}</span>
            <x-badge variant="cyan" size="xs" pill>{{ trans_auto('운영형 카드') }}</x-badge>
            <span>{{ trans_auto('운영 시간·예약 가이드 기반 대표 세션') }}</span>
        </div>
    </section>

    {{-- ═══ 추천 결과 섹션들 ═══ --}}
    @if(!empty($result['sections']))
        @foreach($result['sections'] as $sIdx => $section)
            @if(!empty($section['items']))
            <section class="px-4 animate-fade-up" style="animation-delay: {{ $sIdx * 80 }}ms">
                {{-- 섹션 헤더 --}}
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

                {{-- 카드 리스트 --}}
                <div class="space-y-2.5">
                    @foreach($section['items'] as $item)
                        <x-tonight-card :item="$item" />
                    @endforeach
                </div>
            </section>
            @endif
        @endforeach

        {{-- 재추천 버튼 --}}
        <section class="px-4">
            <a href="{{ route('tonight.quick', $filters) }}"
               class="block w-full text-center btn-secondary px-6 py-3.5 rounded-2xl text-[13px] font-bold">
                다시 추천받기
            </a>
        </section>

    @else
        {{-- ═══ 결과 없음 ═══ --}}
        <section class="px-4">
            <x-empty-state icon="🌙" message="조건에 맞는 추천이 없습니다">
                @if(!empty($result['fallback']))
                    <div class="mt-6 space-y-2.5">
                        @foreach($result['fallback'] as $fb)
                            <a href="{{ $fb['link'] }}"
                               class="flex items-center gap-3 p-3.5 bg-dark-700/50 rounded-2xl border border-white/[0.04] hover:border-accent/20 transition-colors">
                                <span class="text-lg">{{ $fb['icon'] ?? '💡' }}</span>
                                <span class="text-[13px] text-gray-300 font-medium flex-1">{{ $fb['text'] }}</span>
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                            </a>
                        @endforeach
                    </div>
                @endif
            </x-empty-state>
        </section>
    @endif

    {{-- ═══ 빠른 추천 모달 (하단 시트) ═══ --}}
    <div x-show="showQuickModal" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50">
        {{-- 배경 딤 --}}
        <div class="absolute inset-0 bg-black/60" @click="showQuickModal = false"></div>
        {{-- 바텀시트 --}}
        <div x-show="showQuickModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-y-full"
             x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-y-0"
             x-transition:leave-end="translate-y-full"
             class="absolute bottom-0 inset-x-0 max-w-lg mx-auto">
            <div class="bg-dark-900 rounded-t-3xl border-t border-white/[0.06] p-6 safe-bottom">
                {{-- 핸들 --}}
                <div class="w-10 h-1 bg-gray-700 rounded-full mx-auto mb-5"></div>

                <h3 class="text-[18px] font-bold mb-1">빠른 추천</h3>
                <p class="text-[12px] text-gray-500 mb-5">최소 입력으로 바로 추천받으세요</p>

                <form method="GET" action="{{ route('tonight.quick') }}" class="space-y-4">
                    {{-- 지역 --}}
                    <div>
                        <label class="text-[12px] text-gray-400 font-semibold mb-2 block">지역</label>
                        <div class="flex flex-wrap gap-2">
                            <label class="cursor-pointer">
                                <input type="radio" name="area" value="" class="peer hidden" checked>
                                <span class="px-3 py-2 rounded-xl text-[12px] font-semibold bg-dark-700 text-gray-400 border border-white/[0.06] peer-checked:bg-accent peer-checked:text-white peer-checked:border-accent block transition-all">자동 (관심 지역)</span>
                            </label>
                            @foreach($areas as $a)
                            <label class="cursor-pointer">
                                <input type="radio" name="area" value="{{ $a }}" class="peer hidden">
                                <span class="px-3 py-2 rounded-xl text-[12px] font-semibold bg-dark-700 text-gray-400 border border-white/[0.06] peer-checked:bg-accent peer-checked:text-white peer-checked:border-accent block transition-all">{{ $a }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- 장르 (선택) --}}
                    <div>
                        <label class="text-[12px] text-gray-400 font-semibold mb-2 block">장르 <span class="text-gray-600">(선택)</span></label>
                        <div class="flex flex-wrap gap-2">
                            @foreach(array_slice($genres, 0, 6) as $g)
                            <label class="cursor-pointer">
                                <input type="radio" name="genre" value="{{ $g }}" class="peer hidden">
                                <span class="px-3 py-1.5 rounded-lg text-[11px] font-medium bg-dark-700 text-gray-500 border border-white/[0.04] peer-checked:bg-accent/20 peer-checked:text-accent peer-checked:border-accent/30 block transition-all">{{ $g }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- 예산 --}}
                    <div>
                        <label class="text-[12px] text-gray-400 font-semibold mb-2 block">예산</label>
                        <div class="flex gap-2">
                            @foreach([['', '무관'], ['30000', '3만원'], ['50000', '5만원'], ['100000', '10만원']] as [$val, $label])
                            <label class="cursor-pointer flex-1">
                                <input type="radio" name="budget" value="{{ $val }}" class="peer hidden" {{ $val === '' ? 'checked' : '' }}>
                                <span class="block text-center px-2 py-2 rounded-xl text-[12px] font-semibold bg-dark-700 text-gray-400 border border-white/[0.06] peer-checked:bg-accent peer-checked:text-white peer-checked:border-accent transition-all">{{ $label }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- 추천받기 CTA --}}
                    <button type="submit"
                        class="w-full btn-primary py-4 rounded-2xl text-[14px] font-bold text-white flex items-center justify-center gap-2 mt-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                        즉시 추천받기
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ═══ 플로팅 빠른 추천 버튼 ═══ --}}
    <button @click="showQuickModal = true"
        class="fixed bottom-[84px] right-4 z-40 btn-primary w-14 h-14 rounded-2xl flex items-center justify-center shadow-glow animate-glow"
        style="margin-bottom: env(safe-area-inset-bottom);">
        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
    </button>
</div>

@push('scripts')
<script>
function tonightPage() {
    return {
        showQuickModal: false,
    };
}
function tonightFilters() {
    return {};
}
</script>
@endpush
@endsection
