@props([
    'title',
    'subtitle' => null,
    'items' => [],
    'targetType' => null,
    'targetId' => null,
    'ctaHref' => '#detail-inquiry',
    'ctaLabel' => '문의로 확인하기',
])

@php
    $faqItems = collect($items)
        ->filter(fn ($item) => filled($item['question'] ?? null) && filled($item['answer'] ?? null))
        ->values();
@endphp

@if($faqItems->isNotEmpty())
<div x-data="{ open: false }" class="card overflow-hidden border border-white/[0.06] bg-[radial-gradient(circle_at_top_right,rgba(56,189,248,0.18),transparent_40%),linear-gradient(180deg,#171727_0%,#10101a_100%)] p-4">
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-cyan-300/80">FAQ SHEET</p>
            <h3 class="mt-1 text-[16px] font-bold tracking-tight text-white">{{ $title }}</h3>
            @if($subtitle)
                <p class="mt-1 text-[12px] leading-5 text-gray-400">{{ $subtitle }}</p>
            @endif
        </div>
        <button type="button"
                @click="open = true"
                class="shrink-0 rounded-2xl border border-cyan-500/20 bg-cyan-500/10 px-3 py-2 text-[11px] font-semibold text-cyan-200"
                data-track-event="detail_faq_open"
                data-track-target-type="{{ $targetType }}"
                data-track-target-id="{{ $targetId }}"
                data-track-context="faq_summary">
            FAQ 보기
        </button>
    </div>

    <div class="mt-4 grid grid-cols-2 gap-2">
        <div class="rounded-2xl border border-white/[0.06] bg-black/20 px-3 py-3">
            <p class="text-[10px] text-gray-500">확인 항목</p>
            <p class="mt-1 text-[16px] font-bold text-white">{{ $faqItems->count() }}</p>
            <p class="mt-1 text-[10px] text-gray-500">문의 전 체크리스트</p>
        </div>
        <div class="rounded-2xl border border-white/[0.06] bg-black/20 px-3 py-3">
            <p class="text-[10px] text-gray-500">핵심 주제</p>
            <p class="mt-1 text-[12px] font-semibold leading-5 text-white">{{ $faqItems->take(2)->pluck('question')->implode(' · ') }}</p>
        </div>
    </div>

    <div class="mt-3 flex flex-wrap gap-1.5">
        @foreach($faqItems->take(4) as $item)
            <span class="rounded-full border border-white/[0.06] bg-white/[0.04] px-2.5 py-1 text-[10px] font-medium text-gray-300">{{ $item['icon'] ?? '•' }} {{ $item['question'] }}</span>
        @endforeach
    </div>

    <template x-teleport="body">
        <div x-show="open"
             x-cloak
             x-transition.opacity
             class="fixed inset-0 z-[90]"
             @keydown.escape.window="open = false">
            <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="open = false"></div>

            <div class="absolute inset-x-0 bottom-0 max-h-[84vh] overflow-y-auto rounded-t-[30px] border border-white/[0.08] bg-dark-900 px-4 pb-5 pt-4 shadow-2xl">
                <div class="mx-auto mb-4 h-1.5 w-14 rounded-full bg-white/15"></div>

                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-cyan-300/80">OPERATIONS</p>
                        <h4 class="mt-1 text-[18px] font-bold text-white">{{ $title }}</h4>
                        @if($subtitle)
                            <p class="mt-1 text-[12px] leading-5 text-gray-400">{{ $subtitle }}</p>
                        @endif
                    </div>
                    <button type="button"
                            class="flex h-9 w-9 items-center justify-center rounded-full bg-white/[0.06] text-gray-300"
                            @click="open = false">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="mt-4 space-y-3">
                    @foreach($faqItems as $item)
                        <div class="rounded-[24px] border border-white/[0.06] bg-dark-800/90 px-4 py-4">
                            <div class="flex items-start gap-3">
                                <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-cyan-500/10 text-[15px]">
                                    {{ $item['icon'] ?? '•' }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[13px] font-semibold text-white">{{ $item['question'] }}</p>
                                    <p class="mt-1.5 text-[12px] leading-6 text-gray-400">{{ trans_auto($item['answer']) }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <a href="{{ $ctaHref }}"
                   @click="open = false"
                   class="mt-5 flex min-h-12 w-full items-center justify-center rounded-2xl bg-cyan-500 px-4 py-3 text-[13px] font-bold text-white shadow-[0_10px_30px_rgba(6,182,212,0.25)]"
                   data-track-event="detail_faq_cta_click"
                   data-track-target-type="{{ $targetType }}"
                   data-track-target-id="{{ $targetId }}"
                   data-track-context="faq_sheet">
                    {{ $ctaLabel }}
                </a>
            </div>
        </div>
    </template>
</div>
@endif
