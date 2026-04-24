@props(['post', 'compact' => false])

@php
    // 승인된 미디어 이미지 가져오기 (community owner_type)
    $mediaImages = \App\Models\Media::forOwner('community', $post->id)->public()->orderBy('sort_order')->get();
    // JSON images 필드도 합치기 (레거시 지원)
    $legacyImages = collect($post->images ?? [])->filter();
    // 최종 이미지 목록 (media 우선, 없으면 legacy)
    $allImages = $mediaImages->isNotEmpty()
        ? $mediaImages->pluck('file_url')->toArray()
        : $legacyImages->toArray();
    $allThumbs = $mediaImages->isNotEmpty()
        ? $mediaImages->map(fn($m) => $m->thumbnail_url ?? $m->file_url)->toArray()
        : $legacyImages->toArray();
    $allSrcsets = $mediaImages->isNotEmpty()
        ? $mediaImages->pluck('file_srcset')->toArray()
        : $legacyImages->map(fn () => null)->toArray();
    $allThumbSrcsets = $mediaImages->isNotEmpty()
        ? $mediaImages->map(fn($m) => $m->thumbnail_srcset ?? $m->file_srcset)->toArray()
        : $legacyImages->map(fn () => null)->toArray();
    $hasImages = count($allImages) > 0;
@endphp

<div class="card {{ $compact ? 'p-3.5' : 'p-4' }}">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-2.5">
        <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-full gradient-accent flex items-center justify-center shrink-0">
                <span class="text-[11px] font-bold text-white">{{ mb_substr($post->nickname, 0, 1) }}</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="text-[13px] font-semibold text-gray-200">{{ $post->nickname }}</span>
                @if($post->type === 'realtime')
                    <x-badge variant="live" size="xs" pill>
                        <span class="relative flex h-1.5 w-1.5"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span><span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-red-400"></span></span>
                        LIVE
                    </x-badge>
                @elseif($post->type === 'tip')
                    <x-badge variant="blue" size="xs" pill>TIP</x-badge>
                @else
                    <x-badge size="xs" pill>{{ __('community.type_review') }}</x-badge>
                @endif
            </div>
        </div>
        <span class="text-[10px] text-gray-600">{{ $post->created_at->diffForHumans() }}</span>
    </div>

    {{-- Content --}}
    @if($post->title)
        <h3 class="text-[13px] font-semibold text-gray-200 mb-1">{{ trans_auto($post->title) }}</h3>
    @endif
    <p class="text-[12px] text-gray-400 leading-[1.6] {{ $compact ? 'line-clamp-2' : '' }}">{{ trans_auto($post->content) }}</p>

    {{-- Images --}}
    @if($hasImages)
        <x-image-gallery :images="$allImages" :thumbnails="$allThumbs" :srcsets="$allSrcsets" :thumbnailSrcsets="$allThumbSrcsets" :compact="$compact" />
    @endif

    {{-- Footer --}}
    <div class="flex items-center justify-between mt-3 pt-2.5 border-t border-white/[0.03]">
        @if($post->club)
            <a href="{{ route('clubs.show', $post->club) }}" class="text-[11px] text-accent font-medium flex items-center gap-1">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                {{ $post->club->name }}
            </a>
        @else
            <span></span>
        @endif
        @unless($compact)
            <form action="{{ route('community.report', $post) }}" method="POST" class="inline"
                  onsubmit="return confirm('{{ __('community.report_confirm') }}')">
                @csrf
                <button type="submit" class="text-[11px] text-gray-600 hover:text-red-400 transition-colors flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3v1.5M3 21v-6m0 0l2.77-.693a9 9 0 016.208.682l.108.054a9 9 0 006.086.71l3.114-.732a48.524 48.524 0 01-.005-10.499l-3.11.732a9 9 0 01-6.085-.711l-.108-.054a9 9 0 00-6.208-.682L3 4.5M3 15V4.5"/></svg>
                    {{ __('report.btn') }}
                </button>
            </form>
        @endunless
    </div>
</div>
