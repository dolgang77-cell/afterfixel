@props(['md'])

<a href="{{ route('md.show', $md) }}" class="card flex items-center gap-3.5 p-3.5 group">
    @if($md->profile_image)
        @php $profileSrcset = $md->profile_image_srcset; @endphp
        <img src="{{ $md->profile_image }}" class="w-11 h-11 rounded-full object-cover shrink-0 border border-accent/20" loading="lazy" decoding="async"
             @if($profileSrcset) srcset="{{ $profileSrcset }}" sizes="44px" @endif>
    @else
        <div class="w-11 h-11 rounded-full bg-accent/15 flex items-center justify-center shrink-0 text-accent font-bold text-[13px]">{{ mb_substr($md->display_name, 0, 1) }}</div>
    @endif
    <div class="flex-1 min-w-0">
        <p class="text-[13px] font-semibold text-gray-200">{{ $md->display_name }}</p>
        <p class="text-[11px] text-gray-500 truncate">{{ $md->affiliation ?? ($md->areas ? implode(', ', $md->areas) : 'MD') }}</p>
    </div>
    @if($md->external_link)
        <span class="shrink-0 px-2.5 py-1.5 rounded-xl bg-accent/10 border border-accent/20 text-[10px] font-semibold text-accent group-active:scale-95 transition-transform">{{ __('md.contact') }}</span>
    @else
        <svg class="w-4 h-4 text-gray-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
    @endif
</a>
