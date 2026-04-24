@props([
    'variant' => 'warning',
    'icon' => null,
])
@php
    $styles = [
        'warning' => 'bg-amber-500/[0.06] border-amber-500/10 text-amber-300',
        'success' => 'bg-emerald-500/[0.06] border-emerald-500/10 text-emerald-300',
        'error'   => 'bg-red-500/[0.06] border-red-500/10 text-red-300',
        'info'    => 'bg-blue-500/[0.06] border-blue-500/10 text-blue-300',
    ];
    $icons = ['warning' => '⚠️', 'success' => '✅', 'error' => '❌', 'info' => '💡'];
@endphp

<div class="border rounded-2xl px-4 py-3 {{ $styles[$variant] ?? $styles['warning'] }}">
    <p class="text-[13px] leading-relaxed">{{ $icon ?? $icons[$variant] ?? '' }} {{ $slot }}</p>
</div>
