@props(['club', 'layout' => 'horizontal', 'summary' => null, 'trackEvent' => null, 'trackContext' => null])

@if($layout === 'grid')
<a href="{{ route('clubs.show', $club) }}" class="card block overflow-hidden group"
   @if($trackEvent) data-track-event="{{ $trackEvent }}" data-track-target-type="club" data-track-target-id="{{ $club->id }}" data-track-context="{{ $trackContext }}" data-track-label="{{ $club->name }}" @endif>
    <div class="relative h-28 img-overlay">
        @php $thumbnailSrcset = $club->thumbnail_srcset; @endphp
        <img src="{{ $club->thumbnail_url }}" alt="{{ $club->name }}"
             class="w-full h-full object-cover" loading="lazy" decoding="async"
             @if($thumbnailSrcset) srcset="{{ $thumbnailSrcset }}" sizes="(max-width: 640px) 50vw, 240px" @endif>
        @if($club->foreigner_allowed)
            <div class="absolute top-2 right-2 z-10">
                <span class="text-[9px] bg-blue-500/20 text-blue-300 px-1.5 py-0.5 rounded-md font-semibold backdrop-blur-sm border border-blue-500/10">🌍</span>
            </div>
        @endif
    </div>
    <div class="p-3">
        <h3 class="text-[13px] font-bold truncate text-gray-100">{{ $club->name }}</h3>
        <p class="text-[11px] text-gray-500 mt-0.5">{{ trans_auto($club->area) }} · {{ trans_auto($club->genre) }}</p>
        <div class="mt-2 space-y-1.5">
            <x-rating-stars :rating="$club->rating_avg" :count="null" />
            <span class="block text-[10px] text-gray-600 font-medium">{{ $club->entry_fee_text }}</span>
        </div>
    </div>
</a>

@else
<a href="{{ route('clubs.show', $club) }}" class="card flex gap-3.5 overflow-hidden group"
   @if($trackEvent) data-track-event="{{ $trackEvent }}" data-track-target-type="club" data-track-target-id="{{ $club->id }}" data-track-context="{{ $trackContext }}" data-track-label="{{ $club->name }}" @endif>
    <div class="w-[100px] h-[100px] shrink-0 relative img-overlay">
        @php $thumbnailSrcset = $club->thumbnail_srcset; @endphp
        <img src="{{ $club->thumbnail_url }}" alt="{{ $club->name }}"
             class="w-full h-full object-cover" loading="lazy" decoding="async"
             @if($thumbnailSrcset) srcset="{{ $thumbnailSrcset }}" sizes="100px" @endif>
    </div>
    <div class="flex-1 py-3 pr-3.5 min-w-0">
        <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
                <h3 class="text-[13px] font-bold text-gray-100 truncate">{{ $club->name }}</h3>
                <p class="text-[11px] text-gray-500 mt-1">{{ trans_auto($club->area) }} · {{ trans_auto($club->genre) }}{{ $club->subgenre ? ' / '.trans_auto($club->subgenre) : '' }}</p>
            </div>
            <x-rating-stars :rating="$club->rating_avg" :showValue="true" :count="null" />
        </div>
        <div class="mt-2 flex flex-wrap gap-1.5">
            @forelse(($summary['badges'] ?? []) as $badge)
                <x-badge :variant="$badge['variant']" size="xs" pill>{{ trans_auto($badge['label']) }}</x-badge>
            @empty
                <x-badge size="xs" pill>{{ $club->entry_fee_text }}</x-badge>
            @endforelse
        </div>
        <div class="mt-3 grid grid-cols-2 gap-2 text-[11px]">
            <div class="rounded-2xl border border-white/[0.05] bg-white/[0.02] px-3 py-2">
                <p class="text-[10px] text-gray-500">{{ trans_auto('가격대') }}</p>
                <p class="mt-1 font-semibold text-gray-100">{{ $summary['price'] ?? $club->entry_fee_text }}</p>
            </div>
            <div class="rounded-2xl border border-white/[0.05] bg-white/[0.02] px-3 py-2">
                <p class="text-[10px] text-gray-500">{{ trans_auto('응답 속도') }}</p>
                <p class="mt-1 font-semibold text-gray-100">{{ trans_auto($summary['response_text'] ?? '응답 데이터 준비중') }}</p>
            </div>
        </div>
        <div class="mt-2 flex items-center justify-between gap-3 text-[10px] text-gray-500">
            <span class="truncate">{{ trans_auto($summary['support_text'] ?? $club->operating_hours_text) }}</span>
            @if(!empty($summary['highlight_text']))
                <span class="truncate text-right text-accent">{{ trans_auto($summary['highlight_text']) }}</span>
            @endif
        </div>
    </div>
</a>
@endif
