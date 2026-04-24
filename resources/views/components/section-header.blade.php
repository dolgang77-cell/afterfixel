@props([
    'title',
    'action' => null,
    'actionLabel' => '전체보기',
    'badge' => null,
    'pulse' => false,
])

<div class="flex items-center justify-between mb-4">
    <h2 class="text-[17px] font-bold flex items-center gap-2 tracking-tight">
        @if($pulse)
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-pink-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-pink-500"></span>
            </span>
        @endif
        @if($badge)
            <span>{{ $badge }}</span>
        @endif
        {{ $title }}
    </h2>
    @if($action)
        <a href="{{ $action }}" class="text-[11px] text-gray-500 font-medium flex items-center gap-0.5 active:text-accent transition-colors">
            {{ $actionLabel }}
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
        </a>
    @endif
</div>
