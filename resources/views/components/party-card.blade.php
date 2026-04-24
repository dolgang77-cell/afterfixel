@props(['party', 'layout' => 'horizontal', 'summary' => null, 'trackEvent' => null, 'trackContext' => null])

@php
    $timeDisplay = $party->time_range_text;
@endphp

@if($layout === 'carousel')
<a href="{{ route('parties.show', $party) }}" class="snap-start shrink-0 w-[260px] card overflow-hidden group"
   @if($trackEvent) data-track-event="{{ $trackEvent }}" data-track-target-type="party" data-track-target-id="{{ $party->id }}" data-track-context="{{ $trackContext }}" data-track-label="{{ $party->name }}" @endif>
    <div class="relative h-36 img-overlay">
        @php $thumbnailSrcset = $party->thumbnail_srcset; @endphp
        <img src="{{ $party->thumbnail_url }}" alt="{{ $party->name }}"
             class="w-full h-full object-cover" loading="lazy" decoding="async"
             @if($thumbnailSrcset) srcset="{{ $thumbnailSrcset }}" sizes="260px" @endif>
        <div class="absolute top-2.5 left-2.5 z-10 flex gap-1.5">
            @if($party->event_date->isToday())
                <span class="text-[9px] font-bold gradient-accent text-white px-2 py-[3px] rounded-full shadow-glow-sm">TONIGHT</span>
            @endif
            <x-badge :variant="$party->event_card_variant" size="xs" pill>{{ trans_auto($party->event_card_label) }}</x-badge>
            <span class="text-[9px] font-semibold bg-black/40 backdrop-blur-sm text-white/90 px-2 py-[3px] rounded-full border border-white/10">{{ trans_auto($party->genre) }}</span>
        </div>
    </div>
    <div class="p-3.5">
        <h3 class="text-[13px] font-bold truncate text-gray-100">{{ $party->name }}</h3>
        <p class="text-[11px] text-gray-500 mt-1">{{ $party->club?->name }} · {{ trans_auto($party->club?->area ?? '') }}</p>
        <p class="mt-2 line-clamp-2 text-[11px] leading-5 text-gray-400">{{ trans_auto($party->event_card_notice) }}</p>
        <div class="flex items-center justify-between mt-2.5 pt-2.5 border-t border-white/[0.04]">
            <span class="text-[12px] text-accent font-bold">{{ $timeDisplay }}</span>
            <span class="text-[11px] text-gray-400 font-medium">{{ $party->ticket_price_text }}</span>
        </div>
    </div>
</a>

@else
<a href="{{ route('parties.show', $party) }}" class="card overflow-hidden group"
   @if($trackEvent) data-track-event="{{ $trackEvent }}" data-track-target-type="party" data-track-target-id="{{ $party->id }}" data-track-context="{{ $trackContext }}" data-track-label="{{ $party->name }}" @endif>
    <div class="flex gap-3.5">
        <div class="w-[110px] h-[110px] shrink-0 relative img-overlay">
            @php $thumbnailSrcset = $party->thumbnail_srcset; @endphp
            <img src="{{ $party->thumbnail_url }}" alt="{{ $party->name }}"
                 class="w-full h-full object-cover" loading="lazy" decoding="async"
                 @if($thumbnailSrcset) srcset="{{ $thumbnailSrcset }}" sizes="110px" @endif>
            @if($party->event_date->isToday())
                <span class="absolute top-2 left-2 z-10 text-[8px] font-bold gradient-accent text-white px-1.5 py-[2px] rounded-full shadow-glow-sm">TONIGHT</span>
            @endif
        </div>
        <div class="flex-1 py-3 pr-3.5 min-w-0">
            <h3 class="text-[13px] font-bold line-clamp-1 text-gray-100">{{ $party->name }}</h3>
            <p class="text-[11px] text-gray-500 mt-1">{{ $party->club?->name }} · {{ trans_auto($party->club?->area ?? '') }} · {{ trans_auto($party->genre) }}</p>
            <div class="mt-2 flex flex-wrap gap-1.5">
                @forelse(($summary['badges'] ?? []) as $badge)
                    <x-badge :variant="$badge['variant']" size="xs" pill>{{ trans_auto($badge['label']) }}</x-badge>
                @empty
                    <x-badge variant="purple" size="xs" pill>{{ trans_auto($party->genre) }}</x-badge>
                @endforelse
            </div>
            <p class="mt-2 text-[11px] leading-5 text-gray-400 line-clamp-2">{{ trans_auto($party->event_card_notice) }}</p>
            <div class="mt-3 grid grid-cols-2 gap-2 text-[11px]">
                <div class="rounded-2xl border border-white/[0.05] bg-white/[0.02] px-3 py-2">
                    <p class="text-[10px] text-gray-500">{{ trans_auto('가격대') }}</p>
                    <p class="mt-1 font-semibold text-gray-100">{{ $summary['price'] ?? $party->ticket_price_text }}</p>
                </div>
                <div class="rounded-2xl border border-white/[0.05] bg-white/[0.02] px-3 py-2">
                    <p class="text-[10px] text-gray-500">{{ trans_auto('응답 속도') }}</p>
                    <p class="mt-1 font-semibold text-gray-100">{{ trans_auto($summary['response_text'] ?? '응답 데이터 준비중') }}</p>
                </div>
            </div>
            <div class="mt-2 flex items-center justify-between gap-3 text-[10px] text-gray-500">
                <span class="truncate">{{ $summary['support_text'] ?? ($party->event_date->format('n/j').' · '.$timeDisplay) }}</span>
                @if(!empty($summary['highlight_text']))
                    <span class="truncate text-right text-accent">{{ trans_auto($summary['highlight_text']) }}</span>
                @endif
            </div>
        </div>
    </div>
</a>
@endif
