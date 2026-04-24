@props([
    'title',
    'subtitle' => null,
    'mapData' => [],
    'targetType' => null,
])

@php
    $points = collect($mapData['points'] ?? []);
    $hasPoints = (bool) ($mapData['has_points'] ?? false);
    $missingCount = (int) ($mapData['missing_count'] ?? 0);
@endphp

<div class="card overflow-hidden border border-white/[0.06] bg-[radial-gradient(circle_at_top_right,rgba(34,211,238,0.14),transparent_38%),linear-gradient(180deg,#171727_0%,#10101a_100%)] p-4">
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-cyan-300/80">MAP MODE</p>
            <h2 class="mt-1 text-[16px] font-bold tracking-tight text-white">{{ $title }}</h2>
            @if($subtitle)
                <p class="mt-1 text-[12px] leading-5 text-gray-400">{{ $subtitle }}</p>
            @endif
        </div>
        <div class="rounded-2xl border border-white/[0.06] bg-black/20 px-3 py-2 text-right">
            <p class="text-[10px] text-gray-500">좌표 표시</p>
            <p class="mt-1 text-[15px] font-bold text-white">{{ number_format($points->count()) }}</p>
        </div>
    </div>

    @if($hasPoints)
        <div class="mt-4 overflow-hidden rounded-[28px] border border-white/[0.06] bg-[linear-gradient(180deg,rgba(12,18,28,0.95)_0%,rgba(18,28,42,0.92)_100%)] p-3">
            <div class="relative aspect-[0.96] overflow-hidden rounded-[22px] border border-cyan-500/10 bg-[radial-gradient(circle_at_20%_20%,rgba(34,211,238,0.22),transparent_24%),radial-gradient(circle_at_80%_30%,rgba(59,130,246,0.18),transparent_24%),linear-gradient(180deg,rgba(12,18,28,0.96)_0%,rgba(13,22,38,0.96)_100%)]">
                <div class="absolute inset-0 opacity-40" style="background-image: linear-gradient(to right, rgba(255,255,255,0.06) 1px, transparent 1px), linear-gradient(to bottom, rgba(255,255,255,0.06) 1px, transparent 1px); background-size: 28px 28px;"></div>

                <div class="absolute left-4 top-4 rounded-full bg-white/[0.06] px-3 py-1 text-[10px] font-semibold text-gray-300">
                    {{ $points->count() }}곳 표시중
                </div>

                @foreach($points as $point)
                    <a href="{{ $point['href'] }}"
                       class="group absolute -translate-x-1/2 -translate-y-1/2"
                       style="left: {{ $point['x'] }}%; top: {{ 100 - $point['y'] }}%;"
                       data-track-event="list_map_pin_click"
                       data-track-target-type="{{ $targetType }}"
                       data-track-target-id="{{ $point['id'] }}"
                       data-track-context="map_mode">
                        <div class="flex min-w-[42px] items-center justify-center rounded-full border border-cyan-300/30 bg-cyan-400 px-2 py-1 text-[10px] font-extrabold text-slate-950 shadow-[0_10px_24px_rgba(34,211,238,0.25)] transition-transform group-hover:scale-105">
                            {{ $point['index'] }}
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="mt-4 space-y-2">
            @foreach($points->take(6) as $point)
                <a href="{{ $point['href'] }}"
                   class="flex items-center gap-3 rounded-2xl border border-white/[0.06] bg-dark-800/80 px-3 py-3"
                   data-track-event="list_map_item_click"
                   data-track-target-type="{{ $targetType }}"
                   data-track-target-id="{{ $point['id'] }}"
                   data-track-context="map_panel_list">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-cyan-500/12 text-[12px] font-extrabold text-cyan-200">
                        {{ $point['index'] }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-[13px] font-semibold text-white">{{ $point['name'] }}</p>
                        <p class="mt-0.5 truncate text-[11px] text-gray-500">{{ $point['meta'] }}</p>
                    </div>
                    <div class="shrink-0 text-right">
                        <p class="text-[11px] font-semibold text-gray-200">{{ $point['price'] }}</p>
                        @if(!empty($point['badge']))
                            <p class="mt-0.5 text-[10px] text-cyan-300">{{ $point['badge'] }}</p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <div class="mt-4 rounded-[24px] border border-dashed border-white/[0.08] bg-dark-800/70 px-4 py-5 text-[12px] leading-6 text-gray-400">
            현재 필터 결과에는 좌표가 준비된 항목이 없습니다. 지역 필터를 바꾸거나 리스트 보기에서 상세를 먼저 확인해 주세요.
        </div>
    @endif

    @if($missingCount > 0)
        <p class="mt-3 text-[11px] text-gray-500">
            좌표 미등록 항목 {{ number_format($missingCount) }}개는 지도에서 제외되고 리스트에는 그대로 유지됩니다.
        </p>
    @endif
</div>
