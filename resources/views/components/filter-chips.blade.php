@props([
    'items',
    'param',
    'route',
    'allLabel' => '전체',
    'color' => 'purple',
    'selected' => null,
    'bleed' => true,
])

@php
    $activeClass = $color === 'pink'
        ? 'bg-pink-500/15 text-pink-400 border-pink-500/20 shadow-sm'
        : 'gradient-accent text-white border-transparent shadow-glow-sm';
    $inactiveClass = 'bg-dark-700/50 text-gray-500 border-white/[0.04] hover:text-gray-300';
    $containerClass = $bleed
        ? 'flex gap-2 overflow-x-auto scrollbar-hide -mx-4 px-4 py-0.5'
        : 'flex gap-2 overflow-x-auto scrollbar-hide py-0.5';
@endphp

<div class="{{ $containerClass }}">
    <a href="{{ route($route, request()->except($param)) }}"
       class="shrink-0 px-3.5 py-[7px] rounded-full text-[11px] font-semibold border transition-all active:scale-95 {{ !$selected ? $activeClass : $inactiveClass }}"
       data-track-event="list_filter_apply"
       data-track-context="{{ $param }}"
       data-track-label="{{ $allLabel }}"
       data-track-meta='@json(["param" => $param, "value" => null, "route" => $route])'>
        {{ $allLabel }}
    </a>
    @foreach($items as $key => $label)
        @php
            $value = is_numeric($key) ? $label : $key;
            $display = $label;
        @endphp
        <a href="{{ route($route, array_merge(request()->query(), [$param => $value])) }}"
           class="shrink-0 px-3.5 py-[7px] rounded-full text-[11px] font-semibold border transition-all active:scale-95 {{ $selected === (string) $value ? $activeClass : $inactiveClass }}"
           data-track-event="list_filter_apply"
           data-track-context="{{ $param }}"
           data-track-label="{{ trans_auto($display) }}"
           data-track-meta='@json(["param" => $param, "value" => $value, "route" => $route])'>
            {{ trans_auto($display) }}
        </a>
    @endforeach
</div>
