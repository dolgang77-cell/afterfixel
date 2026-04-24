@if ($paginator->hasPages())
<nav class="flex items-center justify-center gap-1.5">
    @if ($paginator->onFirstPage())
        <span class="px-3 py-2 text-[11px] text-gray-700 bg-dark-800 rounded-xl font-medium">이전</span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-2 text-[11px] text-gray-400 bg-dark-700/50 rounded-xl border border-white/[0.04] font-medium active:scale-95 transition-transform">이전</a>
    @endif

    @foreach ($elements as $element)
        @if (is_string($element))
            <span class="px-2 py-2 text-[11px] text-gray-600">{{ $element }}</span>
        @endif
        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span class="w-8 h-8 flex items-center justify-center text-[11px] text-white gradient-accent rounded-xl font-bold shadow-glow-sm">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="w-8 h-8 flex items-center justify-center text-[11px] text-gray-400 bg-dark-700/50 rounded-xl border border-white/[0.04] font-medium active:scale-95 transition-transform">{{ $page }}</a>
                @endif
            @endforeach
        @endif
    @endforeach

    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-2 text-[11px] text-gray-400 bg-dark-700/50 rounded-xl border border-white/[0.04] font-medium active:scale-95 transition-transform">다음</a>
    @else
        <span class="px-3 py-2 text-[11px] text-gray-700 bg-dark-800 rounded-xl font-medium">다음</span>
    @endif
</nav>
@endif
