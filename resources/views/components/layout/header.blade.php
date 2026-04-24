{{-- 상단 고정 헤더 - 앱 스타일 --}}
<header class="fixed top-0 left-0 right-0 z-40 glass border-b border-white/[0.03]">
    <div class="max-w-lg mx-auto px-4 h-14 flex items-center justify-between">
        {{-- Brand --}}
        <a href="/" class="shrink-0 leading-none">
            <span class="text-lg font-extrabold tracking-tight" style="background:linear-gradient(135deg,#6061FF 0%,#5D5EFC 45%,#38D7ED 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent">VYBE</span>
            <span class="block text-[8px] font-semibold uppercase tracking-[0.24em] text-white/35">Tonight Guide</span>
        </a>

        {{-- Center: 내 위치 --}}
        <button x-data="locationBtn()" x-on:click="refresh()" class="flex items-center gap-1.5 text-[12px] text-gray-300 bg-dark-700/60 px-3 py-1.5 rounded-full border border-white/[0.06] active:scale-95 transition-transform max-w-[140px]">
            <svg class="w-3.5 h-3.5 text-accent shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
            </svg>
            <span class="font-medium truncate" x-text="areaName"></span>
            <svg x-show="loading" class="w-3 h-3 text-gray-500 animate-spin shrink-0" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
        </button>

        {{-- Right: Lang + Notification + Search --}}
        <div class="flex items-center gap-1">
            @auth
                @if(auth()->user()->isMd() && auth()->user()->isActive() && auth()->user()->mdProfile)
                    <a href="{{ route('md-dashboard.index') }}" class="h-8 flex items-center gap-1.5 px-2.5 rounded-full bg-indigo-500/15 text-indigo-200 border border-indigo-400/20 active:scale-90 transition-transform">
                        <svg class="w-[14px] h-[14px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5M3.75 12h16.5m-16.5 6.75h16.5"/></svg>
                        <span class="text-[10px] font-bold">MD</span>
                    </a>
                @endif
            @endauth
            {{-- 언어 선택 --}}
            <div class="relative" x-data="{ langOpen: false }">
                <button x-on:click="langOpen = !langOpen" class="h-8 flex items-center gap-1 px-1.5 rounded-full bg-dark-700/40 text-gray-400 active:scale-90 transition-transform">
                    <svg class="w-[14px] h-[14px]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5a17.92 17.92 0 01-8.716-2.247m0 0A8.966 8.966 0 013 12c0-1.97.633-3.794 1.706-5.282"/></svg>
                    <span class="text-[9px] font-semibold uppercase">{{ app()->getLocale() }}</span>
                </button>
                <div x-show="langOpen" x-cloak x-on:click.outside="langOpen = false"
                     x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                     class="absolute right-0 top-10 w-32 bg-dark-800 border border-white/[0.08] rounded-xl shadow-xl overflow-hidden z-50">
                    @foreach(\App\Http\Middleware\SetLocale::enabledLocales() as $code => $loc)
                    <a href="?lang={{ $code }}" x-on:click.prevent="$dispatch('lang-switch', { code: '{{ $code }}', url: '?lang={{ $code }}' })" class="flex items-center justify-between px-3.5 py-2.5 text-[12px] hover:bg-dark-700 transition {{ app()->getLocale() === $code ? 'text-accent font-semibold' : 'text-gray-400' }}">
                        <span>{{ $loc['native'] }}</span>
                        @if(app()->getLocale() === $code)<span class="text-accent">✓</span>@endif
                    </a>
                    @endforeach
                </div>
            </div>

            {{-- 알림 --}}
            <a href="{{ route('notifications.index') }}"
               class="w-8 h-8 flex items-center justify-center rounded-full bg-dark-700/40 text-gray-400 active:scale-90 transition-transform relative"
               x-data="notifBell()" x-init="checkUnread()">
                <svg class="w-[16px] h-[16px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                </svg>
                <span x-show="count > 0" x-cloak
                      class="absolute -top-0.5 -right-0.5 w-4 h-4 rounded-full gradient-accent text-white text-[9px] font-bold flex items-center justify-center leading-none"
                      x-text="count > 9 ? '9+' : count"></span>
            </a>

            {{-- 검색 --}}
            <a href="{{ route('search') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-dark-700/40 text-gray-400 active:scale-90 transition-transform">
                <svg class="w-[16px] h-[16px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                </svg>
            </a>
        </div>
    </div>
</header>
