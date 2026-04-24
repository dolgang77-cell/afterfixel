{{-- 하단 고정 탭바 --}}
@php
    $segment = Request::segment(1);
    $tabs = [
        ['path' => '/',          'seg' => null,        'label' => '홈',      'icon' => 'M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25'],
        ['path' => '/parties',   'seg' => 'parties',   'label' => '파티',    'icon' => 'M9 9l10.5-3m0 6.553v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 11-.99-3.467l2.31-.66a2.25 2.25 0 001.632-2.163zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 01-.99-3.467l2.31-.66A2.25 2.25 0 009 15.553z'],
        ['path' => '/clubs',     'seg' => 'clubs',     'label' => '클럽',    'icon' => 'M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z'],
        ['path' => '/tour',      'seg' => 'tour',      'label' => '루트',    'icon' => 'M9 6.75V15m0-8.25a6 6 0 0112 0v8.25M9 6.75a6 6 0 00-6 6v.75m6-6.75h.008M15 15l4.553 2.276A1 1 0 0021 16.382V7.618a1 1 0 00-.553-.894L15 4.5m0 10.5V4.5m0 0L9 7.5', 'special' => true],
        ['path' => '/my',        'seg' => 'my',        'label' => '마이',    'icon' => 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z'],
    ];
@endphp

<nav class="fixed bottom-0 left-0 right-0 z-50 glass border-t border-white/[0.04] safe-bottom">
    <div class="max-w-lg mx-auto grid grid-cols-5 h-[60px]">
        @foreach($tabs as $tab)
            @php
                if ($tab['seg'] === 'my') {
                    $isActive = in_array($segment, ['my', 'favorites', 'notifications', 'settings']);
                } elseif ($tab['seg']) {
                    $isActive = $segment === $tab['seg'];
                } else {
                    $isActive = !$segment || $segment === '';
                }
            @endphp
            <a href="{{ $tab['path'] }}"
               class="tab-item flex flex-col items-center justify-center gap-[3px] {{ $isActive ? 'tab-active' : '' }}">
                <div class="relative">
                    <svg class="w-[22px] h-[22px]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $tab['icon'] }}"/>
                    </svg>
                    @if(!empty($tab['special']))
                        <span class="absolute -top-1.5 -right-2.5 text-[7px] font-black gradient-accent text-white px-[5px] py-[1px] rounded-full leading-[12px] shadow-glow-sm">AI</span>
                    @endif
                </div>
                <span class="text-[10px] font-medium leading-none">{{ $tab['label'] }}</span>
            </a>
        @endforeach
    </div>
</nav>
