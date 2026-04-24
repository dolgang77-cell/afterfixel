@extends('layouts.app')
@section('title', __('notification.title'))

@section('content')
<div class="px-4 py-6">
    <div class="flex items-center justify-between mb-5">
        <div class="flex items-center gap-3">
            <a href="{{ url()->previous() }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-dark-700/60 text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            </a>
            <h1 class="text-[20px] font-extrabold text-white">{{ __('notification.title') }}</h1>
        </div>
        <div class="flex items-center gap-3">
            @if($notifications->total() > 0)
                <form action="{{ route('notifications.mark-all-read') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-[12px] text-accent hover:text-accent-light">{{ __('notification.mark_all') }}</button>
                </form>
            @endif
            <a href="{{ route('notification-settings.edit') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-dark-700/60 text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 010 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 010-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </a>
        </div>
    </div>

    @php
        $typeIcons = [
            'party_reminder'        => ['icon' => '⏰', 'color' => 'purple'],
            'new_party_match'       => ['icon' => '🎉', 'color' => 'pink'],
            'tonight_recommendation' => ['icon' => '🌙', 'color' => 'blue'],
        ];
    @endphp

    <div class="space-y-2.5">
        @forelse($notifications as $notif)
            @php $meta = $typeIcons[$notif->type] ?? ['icon' => '🔔', 'color' => 'gray']; @endphp
            <a href="{{ $notif->link ? route('notifications.read', $notif) : '#' }}"
               class="card p-4 block {{ !$notif->is_read ? 'border-l-2 border-l-accent' : '' }}"
               @if($notif->link && !$notif->is_read)
                   onclick="event.preventDefault(); document.getElementById('read-{{ $notif->id }}').submit();"
               @endif
            >
                <div class="flex gap-3">
                    <div class="w-9 h-9 rounded-xl bg-{{ $meta['color'] }}-500/10 flex items-center justify-center text-[16px] shrink-0">
                        {{ $meta['icon'] }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-[13px] font-semibold text-white {{ !$notif->is_read ? '' : 'text-gray-300' }}">{{ $notif->title }}</p>
                            @if(!$notif->is_read)
                                <span class="w-2 h-2 rounded-full bg-accent shrink-0 mt-1.5"></span>
                            @endif
                        </div>
                        @if(!empty($notif->event_context['badges']))
                            <div class="mt-1.5 flex flex-wrap gap-1.5">
                                @foreach($notif->event_context['badges'] as $badge)
                                    <x-badge :variant="$badge['variant']" size="xs" pill>{{ trans_auto($badge['label']) }}</x-badge>
                                @endforeach
                            </div>
                        @endif
                        <p class="text-[12px] text-gray-400 mt-0.5 line-clamp-2">{{ $notif->body }}</p>
                        @if(!empty($notif->event_context['notice']))
                            <p class="text-[11px] text-gray-500 mt-1 line-clamp-2">{{ trans_auto($notif->event_context['notice']) }}</p>
                        @endif
                        <p class="text-[10px] text-gray-600 mt-1.5">{{ $notif->sent_at?->diffForHumans() }}</p>
                    </div>
                </div>
            </a>
            @if($notif->link && !$notif->is_read)
                <form id="read-{{ $notif->id }}" action="{{ route('notifications.read', $notif) }}" method="POST" class="hidden">@csrf</form>
            @endif
        @empty
            <x-empty-state :message="__('notification.empty')" />
        @endforelse
    </div>

    <div class="mt-4">{{ $notifications->links() }}</div>
</div>
@endsection
