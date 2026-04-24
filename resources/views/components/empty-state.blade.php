@props([
    'icon' => null,
    'message',
    'action' => null,
    'actionLabel' => '',
])

<div class="bg-dark-800/50 rounded-3xl p-10 text-center border border-white/[0.03]">
    @if($icon)
        <div class="text-5xl mb-4 animate-float">{{ $icon }}</div>
    @else
        <div class="w-12 h-12 mx-auto mb-4 rounded-2xl bg-dark-700/50 flex items-center justify-center">
            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
            </svg>
        </div>
    @endif
    <p class="text-gray-500 text-sm mb-1">{{ $message }}</p>
    @if($action)
        <a href="{{ $action }}" class="text-accent text-sm font-medium mt-3 inline-flex items-center gap-1 hover:underline">
            {{ $actionLabel }}
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
        </a>
    @endif
    {{ $slot }}
</div>
