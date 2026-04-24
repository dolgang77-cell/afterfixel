@props(['item', 'compact' => false])

@php
    $entity = $item['entity'];
    $isParty = $item['type'] === 'party';
    $labels = $item['labels'] ?? [];
    $reasons = $item['reasons'] ?? [];
    $travelMin = $item['travel_min'] ?? null;
    $cost = $item['cost'] ?? null;
    $costText = $item['cost_text'] ?? ($cost ? number_format($cost) . '원~' : '');
    $cta = $item['cta'] ?? __('common.view_detail');

    $link = $item['link'] ?? ($isParty
        ? route('parties.show', $entity)
        : route('clubs.show', $entity));

    $name = $entity->name;
    $subtitle = $isParty
        ? ($entity->club?->name . ' · ' . trans_auto($entity->club?->area ?? ''))
        : (trans_auto($entity->area) . ' · ' . trans_auto($entity->genre));
    $thumb = $entity->thumbnail_url;
    $timeDisplay = $item['time_display'] ?? ($isParty ? $entity->time_range_text : $entity->operating_hours_text);

    $labelColors = [
        'green'  => 'bg-green-500/15 text-green-400 border-green-500/20',
        'orange' => 'bg-orange-500/15 text-orange-400 border-orange-500/20',
        'red'    => 'bg-red-500/15 text-red-400 border-red-500/20',
        'blue'   => 'bg-blue-500/15 text-blue-400 border-blue-500/20',
        'yellow' => 'bg-yellow-500/15 text-yellow-400 border-yellow-500/20',
        'cyan'   => 'bg-cyan-500/15 text-cyan-400 border-cyan-500/20',
        'purple' => 'bg-purple-500/15 text-purple-400 border-purple-500/20',
        'gray'   => 'bg-gray-500/15 text-gray-400 border-gray-500/20',
    ];

    $reason = \App\Services\TonightService::generateTonightReason($item, null);
    $availabilitySummary = $item['availability_summary'] ?? null;
    $eventCardLabel = $isParty ? ($entity->event_card_label ?? null) : null;
    $eventCardNotice = $isParty ? ($entity->event_card_notice ?? null) : null;
    $eventCardVariantClass = match ($entity->event_card_variant ?? null) {
        'green' => 'bg-emerald-500/15 text-emerald-300 border-emerald-500/20',
        'cyan' => 'bg-cyan-500/15 text-cyan-300 border-cyan-500/20',
        default => 'bg-white/10 text-gray-300 border-white/10',
    };
@endphp

@if($compact)
{{-- 컴팩트 카드 (홈 캐러셀용) --}}
<a href="{{ $link }}" class="snap-start shrink-0 w-[200px] card overflow-hidden group">
    <div class="relative h-28 img-overlay">
        <img src="{{ $thumb }}" alt="{{ $name }}" class="w-full h-full object-cover" loading="lazy">
        {{-- 상태 뱃지 --}}
        <div class="absolute top-2 left-2 z-10 flex flex-wrap gap-1">
            @foreach(array_slice($labels, 0, 2) as $label)
                <span class="text-[8px] font-bold px-1.5 py-[2px] rounded-full border {{ $labelColors[$label['color']] ?? '' }}">{{ $label['text'] }}</span>
            @endforeach
        </div>
        {{-- 타입 뱃지 --}}
        <div class="absolute top-2 right-2 z-10">
            <span class="text-[8px] font-bold px-1.5 py-[2px] rounded-full bg-dark-950/60 text-gray-300 border border-white/10">
                {{ $isParty ? __('nav.party') : __('nav.club') }}
            </span>
        </div>
    </div>
    <div class="p-3">
        <p class="text-[12px] font-bold text-gray-100 truncate">{{ $name }}</p>
        <p class="text-[10px] text-gray-500 mt-0.5 truncate">{{ $subtitle }}</p>
        @if($eventCardLabel)
            <div class="mt-1.5">
                <span class="text-[8px] font-bold px-1.5 py-[2px] rounded-full border {{ $eventCardVariantClass }}">{{ trans_auto($eventCardLabel) }}</span>
            </div>
        @endif
        {{-- 추천 이유 (한 줄) --}}
        <p class="text-[9px] text-accent/70 mt-1 truncate">{{ $reason }}</p>
        @if($eventCardNotice)
            <p class="text-[9px] text-gray-500 mt-1 truncate">{{ trans_auto($eventCardNotice) }}</p>
        @endif
        @if($availabilitySummary)
            <p class="text-[9px] text-gray-500 mt-1 truncate">{{ $availabilitySummary }}</p>
        @endif
        <div class="flex items-center justify-between mt-2 pt-2 border-t border-white/[0.04]">
            <span class="text-[11px] text-accent font-bold">{{ $timeDisplay ?: '' }}</span>
            @if($costText)
                <span class="text-[10px] text-gray-400">{{ $costText }}</span>
            @endif
        </div>
    </div>
</a>

@else
{{-- 풀 카드 (전용 페이지용) --}}
<a href="{{ $link }}" class="card overflow-hidden group block">
    <div class="flex gap-3.5">
        <div class="w-[110px] h-[130px] shrink-0 relative img-overlay">
            <img src="{{ $thumb }}" alt="{{ $name }}" class="w-full h-full object-cover" loading="lazy">
            {{-- 상태 뱃지 --}}
            <div class="absolute top-2 left-2 z-10 flex flex-col gap-1">
                @foreach(array_slice($labels, 0, 2) as $label)
                    <span class="text-[8px] font-bold px-1.5 py-[2px] rounded-full border inline-block {{ $labelColors[$label['color']] ?? '' }}">{{ $label['text'] }}</span>
                @endforeach
            </div>
            {{-- 타입 뱃지 --}}
            <div class="absolute bottom-2 left-2 z-10">
                <span class="text-[8px] font-bold px-1.5 py-[2px] rounded-full bg-dark-950/60 text-gray-300 border border-white/10">
                    {{ $isParty ? __('nav.party') : __('nav.club') }}
                </span>
            </div>
        </div>
        <div class="flex-1 py-3 pr-3.5 min-w-0 flex flex-col justify-between">
            <div>
                <h3 class="text-[14px] font-bold text-gray-100 truncate">{{ $name }}</h3>
                <p class="text-[11px] text-gray-500 mt-0.5">{{ $subtitle }}</p>
                @if($eventCardLabel)
                    <div class="mt-1.5">
                        <span class="text-[9px] font-bold px-2 py-[3px] rounded-full border {{ $eventCardVariantClass }}">{{ trans_auto($eventCardLabel) }}</span>
                    </div>
                @endif
                {{-- 추천 이유 --}}
                <p class="text-[10px] text-accent/80 mt-1.5 line-clamp-2 leading-relaxed">{{ $reason }}</p>
                @if($eventCardNotice)
                    <p class="text-[10px] text-gray-500 mt-1 line-clamp-2 leading-relaxed">{{ trans_auto($eventCardNotice) }}</p>
                @endif
                @if($availabilitySummary)
                    <p class="text-[10px] text-gray-500 mt-1 line-clamp-1">{{ $availabilitySummary }}</p>
                @endif
            </div>
            <div>
                {{-- 정보 행 --}}
                <div class="flex items-center gap-3 mt-2 text-[10px] text-gray-500">
                    @if($timeDisplay)
                        <span class="flex items-center gap-0.5">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $timeDisplay }}
                        </span>
                    @endif
                    @if($costText)
                        <span>{{ $costText }}</span>
                    @endif
                    @if($travelMin !== null && $travelMin > 0)
                        <span class="flex items-center gap-0.5">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                            {{ $travelMin }}분
                        </span>
                    @endif
                </div>
                {{-- CTA --}}
                <div class="mt-2 pt-2 border-t border-white/[0.04]">
                    <span class="text-[11px] text-accent font-bold flex items-center gap-1">
                        {{ $cta }}
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                    </span>
                </div>
            </div>
        </div>
    </div>
</a>
@endif
