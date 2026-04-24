@props([
    'image',
    'alt' => '',
    'backRoute',
    'height' => 'h-56',
])

<div class="relative {{ $height }} img-overlay">
    <img src="{{ $image }}" alt="{{ $alt }}" class="w-full h-full object-cover">
    <a href="{{ $backRoute }}"
       class="absolute top-4 left-4 z-10 w-9 h-9 bg-black/40 rounded-full flex items-center justify-center backdrop-blur-md border border-white/10 active:scale-90 transition-transform">
        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
        </svg>
    </a>
    {{ $slot }}
</div>
