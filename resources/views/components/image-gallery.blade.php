{{--
  이미지 갤러리 컴포넌트
  - compact: 목록 카드용 (작은 썸네일)
  - full: 상세 페이지용 (그리드 + 라이트박스)
--}}
@props(['images' => [], 'thumbnails' => [], 'srcsets' => [], 'thumbnailSrcsets' => [], 'compact' => false])

@if(count($images) > 0)
@php
    $thumbs = count($thumbnails) === count($images) ? $thumbnails : $images;
    $imgSrcsets = count($srcsets) === count($images) ? $srcsets : array_fill(0, count($images), null);
    $thumbSrcsets = count($thumbnailSrcsets) === count($thumbs) ? $thumbnailSrcsets : $imgSrcsets;
    $count = count($images);
@endphp

<div x-data="{ lightbox: false, idx: 0, imgs: {{ json_encode($images) }}, touchStartX: 0 }" class="{{ $compact ? 'mt-2.5' : 'mt-3' }}">

    @if($compact)
    {{-- ═══ 목록용 컴팩트 ═══ --}}
    @if($count === 1)
        {{-- 1장: 둥근 카드형 --}}
        <button x-on:click="idx=0; lightbox=true" class="block w-full rounded-xl overflow-hidden">
            <img src="{{ $thumbs[0] }}" class="w-full h-40 object-cover" loading="lazy" decoding="async" alt=""
                 @if($thumbSrcsets[0]) srcset="{{ $thumbSrcsets[0] }}" sizes="(max-width: 640px) 100vw, 640px" @endif>
        </button>
    @elseif($count === 2)
        {{-- 2장: 나란히 --}}
        <div class="grid grid-cols-2 gap-1.5">
            @foreach([0, 1] as $i)
            <button x-on:click="idx={{ $i }}; lightbox=true" class="rounded-xl overflow-hidden">
                <img src="{{ $thumbs[$i] }}" class="w-full h-28 object-cover" loading="lazy" decoding="async" alt=""
                     @if($thumbSrcsets[$i]) srcset="{{ $thumbSrcsets[$i] }}" sizes="(max-width: 640px) 50vw, 320px" @endif>
            </button>
            @endforeach
        </div>
    @elseif($count === 3)
        {{-- 3장: 왼쪽 크게 + 오른쪽 2개 --}}
        <div class="grid grid-cols-3 gap-1.5 h-36">
            <button x-on:click="idx=0; lightbox=true" class="col-span-2 rounded-xl overflow-hidden">
                <img src="{{ $thumbs[0] }}" class="w-full h-full object-cover" loading="lazy" decoding="async" alt=""
                     @if($thumbSrcsets[0]) srcset="{{ $thumbSrcsets[0] }}" sizes="(max-width: 640px) 66vw, 420px" @endif>
            </button>
            <div class="flex flex-col gap-1.5">
                <button x-on:click="idx=1; lightbox=true" class="flex-1 rounded-xl overflow-hidden">
                    <img src="{{ $thumbs[1] }}" class="w-full h-full object-cover" loading="lazy" decoding="async" alt=""
                         @if($thumbSrcsets[1]) srcset="{{ $thumbSrcsets[1] }}" sizes="(max-width: 640px) 33vw, 220px" @endif>
                </button>
                <button x-on:click="idx=2; lightbox=true" class="flex-1 rounded-xl overflow-hidden">
                    <img src="{{ $thumbs[2] }}" class="w-full h-full object-cover" loading="lazy" decoding="async" alt=""
                         @if($thumbSrcsets[2]) srcset="{{ $thumbSrcsets[2] }}" sizes="(max-width: 640px) 33vw, 220px" @endif>
                </button>
            </div>
        </div>
    @else
        {{-- 4장+: 2×2 그리드 + 카운트 --}}
        <div class="grid grid-cols-2 gap-1.5">
            @foreach([0, 1, 2, 3] as $i)
            <button x-on:click="idx={{ $i }}; lightbox=true" class="relative rounded-xl overflow-hidden h-24">
                <img src="{{ $thumbs[$i] ?? $thumbs[0] }}" class="w-full h-full object-cover" loading="lazy" decoding="async" alt=""
                     @if($thumbSrcsets[$i] ?? null) srcset="{{ $thumbSrcsets[$i] }}" sizes="(max-width: 640px) 50vw, 320px" @endif>
                @if($i === 3 && $count > 4)
                <div class="absolute inset-0 bg-black/50 flex items-center justify-center backdrop-blur-[2px]">
                    <span class="text-white text-[15px] font-bold">+{{ $count - 3 }}</span>
                </div>
                @endif
            </button>
            @endforeach
        </div>
    @endif

    @else
    {{-- ═══ 상세용 갤러리 ═══ --}}
    @if($count === 1)
        <button x-on:click="idx=0; lightbox=true" class="block w-full rounded-xl overflow-hidden">
            <img src="{{ $thumbs[0] }}" class="block w-full max-h-64 object-cover" loading="lazy" decoding="async" alt=""
                 @if($thumbSrcsets[0]) srcset="{{ $thumbSrcsets[0] }}" sizes="(max-width: 640px) 100vw, 720px" @endif>
        </button>
    @elseif($count === 2)
        <div class="grid grid-cols-2 gap-2">
            @foreach([0, 1] as $i)
            <button x-on:click="idx={{ $i }}; lightbox=true" class="block w-full rounded-xl overflow-hidden">
                <img src="{{ $thumbs[$i] }}" class="block w-full h-36 object-cover" loading="lazy" decoding="async" alt=""
                     @if($thumbSrcsets[$i]) srcset="{{ $thumbSrcsets[$i] }}" sizes="(max-width: 640px) 50vw, 360px" @endif>
            </button>
            @endforeach
        </div>
    @else
        {{-- 3장+: 왼쪽 메인 + 오른쪽 서브 --}}
        <div class="grid h-48 grid-cols-3 gap-2 overflow-hidden">
            <button x-on:click="idx=0; lightbox=true" class="col-span-2 block h-full min-h-0 w-full rounded-xl overflow-hidden">
                <img src="{{ $thumbs[0] }}" class="block h-full w-full object-cover" loading="lazy" decoding="async" alt=""
                     @if($thumbSrcsets[0]) srcset="{{ $thumbSrcsets[0] }}" sizes="(max-width: 640px) 66vw, 520px" @endif>
            </button>
            <div class="flex min-h-0 flex-col gap-2 overflow-hidden">
                @foreach(array_slice(range(1, min($count-1, 3)), 0, 3) as $i)
                <button x-on:click="idx={{ $i }}; lightbox=true" class="relative block h-full min-h-0 w-full flex-1 overflow-hidden rounded-xl">
                    <img src="{{ $thumbs[$i] ?? $thumbs[0] }}" class="block h-full w-full object-cover" loading="lazy" decoding="async" alt=""
                         @if($thumbSrcsets[$i] ?? null) srcset="{{ $thumbSrcsets[$i] }}" sizes="(max-width: 640px) 33vw, 240px" @endif>
                    @if($i === min($count-1, 3) && $count > 4)
                    <div class="absolute inset-0 bg-black/50 flex items-center justify-center backdrop-blur-[2px]">
                        <span class="text-white text-[14px] font-bold">+{{ $count - 3 }}</span>
                    </div>
                    @endif
                </button>
                @endforeach
            </div>
        </div>
    @endif
    @endif

    {{-- ═══ 라이트박스 ═══ --}}
    <template x-teleport="body">
        <div x-show="lightbox" x-cloak
             x-on:keydown.escape.window="lightbox=false"
             x-on:keydown.left.window="idx = idx > 0 ? idx-1 : imgs.length-1"
             x-on:keydown.right.window="idx = idx < imgs.length-1 ? idx+1 : 0"
             class="fixed inset-0 z-[9999] bg-black/95 flex flex-col"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">

            {{-- 상단 바 --}}
            <div class="flex items-center justify-between px-4 py-3 shrink-0">
                <span class="text-white/70 text-[13px] font-medium" x-text="(idx+1) + ' {{ __("gallery.of") }} ' + imgs.length"></span>
                <button x-on:click="lightbox=false" class="w-9 h-9 flex items-center justify-center rounded-full bg-white/10 text-white active:scale-90 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- 이미지 영역 --}}
            <div class="flex-1 flex items-center justify-center px-2 relative overflow-hidden"
                 x-on:touchstart="touchStartX = $event.changedTouches[0].screenX"
                 x-on:touchend="let diff=$event.changedTouches[0].screenX-touchStartX; if(diff>50) idx=idx>0?idx-1:imgs.length-1; if(diff<-50) idx=idx<imgs.length-1?idx+1:0;">
                <img :src="imgs[idx]" class="max-w-full max-h-full object-contain rounded-lg select-none" alt="" draggable="false">

                {{-- 좌우 버튼 --}}
                <template x-if="imgs.length > 1">
                    <div>
                        <button x-on:click="idx=idx>0?idx-1:imgs.length-1" class="absolute left-1 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-white/10 text-white/80 flex items-center justify-center hover:bg-white/20 transition hidden sm:flex">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                        </button>
                        <button x-on:click="idx=idx<imgs.length-1?idx+1:0" class="absolute right-1 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-white/10 text-white/80 flex items-center justify-center hover:bg-white/20 transition hidden sm:flex">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                        </button>
                    </div>
                </template>
            </div>

            {{-- 하단 도트 인디케이터 (5장 이하) 또는 썸네일 스트립 --}}
            <template x-if="imgs.length > 1 && imgs.length <= 5">
                <div class="flex gap-1.5 justify-center py-4 shrink-0">
                    <template x-for="(img, i) in imgs" :key="i">
                        <button x-on:click="idx=i" class="w-2 h-2 rounded-full transition-all"
                                :class="i === idx ? 'bg-white w-5' : 'bg-white/30'"></button>
                    </template>
                </div>
            </template>
            <template x-if="imgs.length > 5">
                <div class="flex gap-1.5 px-4 py-3 overflow-x-auto scrollbar-hide justify-center shrink-0">
                    <template x-for="(img, i) in imgs" :key="i">
                        <button x-on:click="idx=i" class="w-11 h-11 rounded-lg overflow-hidden shrink-0 border-2 transition-all"
                                :class="i === idx ? 'border-accent scale-110' : 'border-transparent opacity-40'">
                            <img :src="img" class="w-full h-full object-cover" alt="">
                        </button>
                    </template>
                </div>
            </template>
        </div>
    </template>
</div>
@endif
