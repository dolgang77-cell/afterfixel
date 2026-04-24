@props([
    'variant' => 'default',
    'size' => 'sm',
    'pill' => false,
])

@php
    $variants = [
        'default' => 'bg-white/[0.06] text-gray-400 border border-white/[0.04]',
        'purple'  => 'bg-purple-500/10 text-purple-400 border border-purple-500/10',
        'pink'    => 'gradient-accent text-white border-0 shadow-glow-sm',
        'blue'    => 'bg-blue-500/10 text-blue-400 border border-blue-500/10',
        'cyan'    => 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/10',
        'red'     => 'bg-red-500/10 text-red-400 border border-red-500/10',
        'orange'  => 'bg-orange-500/10 text-orange-300 border border-orange-500/10',
        'yellow'  => 'bg-amber-500/10 text-amber-400 border border-amber-500/10',
        'green'   => 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/10',
        'tag'     => 'bg-purple-500/8 text-purple-300 border border-purple-500/8',
        'live'    => 'bg-red-500/15 text-red-400 border border-red-500/15',
    ];
    $sizes = [
        'xs' => 'text-[9px] px-1.5 py-[2px] leading-[14px]',
        'sm' => 'text-[10px] px-2 py-[3px] leading-[15px]',
        'md' => 'text-[11px] px-2.5 py-1 leading-[16px]',
    ];
    $classes = ($variants[$variant] ?? $variants['default'])
        . ' ' . ($sizes[$size] ?? $sizes['sm'])
        . ' ' . ($pill ? 'rounded-full' : 'rounded-lg')
        . ' font-semibold inline-flex items-center gap-1 whitespace-nowrap';
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</span>
