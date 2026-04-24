@extends('layouts.app')
@section('title', $type === 'club' ? '클럽 비교' : '파티 비교')
@section('page_type', 'compare')

@section('content')
<div class="px-4 py-5 space-y-4 pb-28">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h1 class="text-[20px] font-extrabold tracking-tight text-white">{{ $type === 'club' ? '클럽 비교' : '파티 비교' }}</h1>
            <p class="mt-1 text-[11px] text-gray-500">최대 {{ $maxItems }}개까지 같은 기준으로 한 번에 비교합니다.</p>
        </div>
        @if($items->isNotEmpty())
            <form action="{{ route('compare.clear', ['type' => $type]) }}" method="POST">
                @csrf
                <button type="submit" class="rounded-2xl border border-white/[0.08] bg-dark-800 px-3 py-2 text-[11px] font-semibold text-gray-300">비우기</button>
            </form>
        @endif
    </div>

    @if($items->count() < 2)
        <div class="card p-5 text-center">
            <p class="text-[15px] font-bold text-white">비교 후보가 아직 부족합니다.</p>
            <p class="mt-2 text-[12px] text-gray-500">리스트에서 2개 이상 담아야 차이를 바로 볼 수 있습니다.</p>
            <div class="mt-4 flex gap-2">
                <a href="{{ $type === 'club' ? route('clubs.index') : route('parties.index') }}" class="btn-primary flex-1 rounded-2xl px-4 py-3 text-[13px] font-bold text-white">후보 더 담기</a>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 gap-3">
            @foreach($comparisonRows as $row)
                <div class="card overflow-hidden">
                    <div class="relative h-32">
                        <img src="{{ $row['image_url'] }}" alt="{{ $row['headline'] }}" class="h-full w-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-dark-950 via-dark-950/45 to-transparent"></div>
                        <div class="absolute bottom-3 left-3 right-3 flex items-end justify-between gap-3">
                            <div class="min-w-0">
                                @if($type === 'party')
                                    <div class="mb-2">
                                        <x-badge :variant="$row['item']->event_card_variant" size="xs" pill>{{ $row['item']->event_card_label }}</x-badge>
                                    </div>
                                @endif
                                <p class="truncate text-[15px] font-bold text-white">{{ $row['headline'] }}</p>
                                <p class="mt-1 truncate text-[11px] text-gray-300">{{ $row['subline'] }}</p>
                            </div>
                            <form action="{{ route('compare.toggle', ['type' => $type, 'id' => $row['item']->id]) }}" method="POST">
                                @csrf
                                <button type="submit" class="rounded-full bg-black/35 p-2 text-white backdrop-blur-sm">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="space-y-2 p-4">
                        @if($type === 'party')
                            <div class="rounded-2xl border border-white/[0.05] bg-white/[0.02] px-3 py-2.5">
                                <p class="text-[11px] text-gray-400">{{ $row['item']->event_card_notice }}</p>
                            </div>
                        @endif
                        @foreach($comparisonLabels as $label)
                            <div class="flex items-center justify-between gap-3 rounded-2xl border border-white/[0.05] bg-white/[0.02] px-3 py-2.5">
                                <span class="text-[11px] text-gray-500">{{ $label }}</span>
                                <span class="text-right text-[12px] font-semibold text-white">{{ $row['facts'][$label] ?: '-' }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="px-4 pb-4">
                        <a href="{{ $row['detail_url'] }}" class="btn-primary flex w-full items-center justify-center rounded-2xl px-4 py-3 text-[12px] font-bold text-white">
                            상세 보기
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
