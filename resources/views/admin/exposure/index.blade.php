@extends('admin.layouts.app')
@section('title', '홈 노출 관리')

@section('content')
<h1 class="text-2xl font-bold text-gray-800 mb-6">홈 노출 / 우선순위 관리</h1>

<div class="grid lg:grid-cols-2 gap-6">
    {{-- ═══ 클럽 노출 순서 ═══ --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="font-semibold text-gray-800 mb-1">클럽 노출 순서</h2>
        <p class="text-xs text-gray-400 mb-4">숫자가 클수록 상단에 노출됩니다. 0은 기본 (평점순).</p>

        <form method="POST" action="{{ route('admin.exposure.club-order') }}">
            @csrf
            <div class="space-y-2 max-h-[480px] overflow-y-auto">
                @foreach($clubs as $club)
                    <div class="flex items-center gap-3 py-2 px-3 rounded-lg hover:bg-gray-50 border border-gray-100">
                        <input type="number" name="orders[{{ $club->id }}]" value="{{ $club->sort_order }}"
                               min="0" class="w-16 px-2 py-1 border rounded text-sm text-center">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $club->name }}</p>
                            <p class="text-xs text-gray-400">{{ $club->area }} · {{ $club->genre }}</p>
                        </div>
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $club->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $club->is_active ? '활성' : '비활성' }}
                        </span>
                        <span class="text-xs text-gray-400" title="평점">{{ $club->rating_avg }}</span>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 pt-4 border-t">
                <button type="submit" class="px-4 py-2 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700">순서 일괄 저장</button>
            </div>
        </form>
    </div>

    {{-- ═══ 파티 홈 고정 ═══ --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="font-semibold text-gray-800 mb-1">파티 홈 고정</h2>
        <p class="text-xs text-gray-400 mb-4">고정된 파티는 홈 "오늘의 파티" 섹션 상단에 노출됩니다.</p>

        <div class="space-y-2 max-h-[480px] overflow-y-auto">
            @forelse($parties as $party)
                <div class="flex items-center gap-3 py-2 px-3 rounded-lg hover:bg-gray-50 border border-gray-100">
                    <form method="POST" action="{{ route('admin.exposure.party-pin', $party) }}" class="shrink-0">
                        @csrf @method('PATCH')
                        <button type="submit" class="w-8 h-8 rounded-lg flex items-center justify-center text-sm transition-colors
                            {{ ($party->sort_order ?? 0) > 0 ? 'bg-purple-100 text-purple-600' : 'bg-gray-100 text-gray-400 hover:bg-gray-200' }}"
                            title="{{ ($party->sort_order ?? 0) > 0 ? '고정 해제' : '고정' }}">
                            {{ ($party->sort_order ?? 0) > 0 ? '📌' : '📍' }}
                        </button>
                    </form>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $party->name }}</p>
                        <p class="text-xs text-gray-400">{{ $party->club?->name }} · {{ $party->event_date?->format('m/d') }} {{ $party->start_time }}</p>
                    </div>
                    @php
                        $statusColors = [
                            'upcoming'  => 'bg-blue-100 text-blue-700',
                            'ongoing'   => 'bg-green-100 text-green-700',
                            'ended'     => 'bg-gray-100 text-gray-500',
                            'cancelled' => 'bg-red-100 text-red-600',
                        ];
                    @endphp
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $statusColors[$party->status] ?? '' }}">
                        {{ $party->status }}
                    </span>
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-4">예정된 파티가 없습니다.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- ═══ 배너 위치별 관리 ═══ --}}
<div class="mt-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-gray-800">배너 노출 현황</h2>
        <a href="{{ route('admin.banners.create') }}" class="px-3 py-1.5 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700">+ 배너 추가</a>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($positionLabels as $pos => $label)
            <div class="bg-white rounded-xl shadow-sm p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">{{ $label }}</h3>
                @if(isset($banners[$pos]) && $banners[$pos]->count())
                    <div class="space-y-2">
                        @foreach($banners[$pos] as $banner)
                            <div class="flex items-center gap-2 p-2 rounded-lg {{ $banner->is_active ? 'bg-green-50 border border-green-100' : 'bg-gray-50 border border-gray-100' }}">
                                <span class="text-xs text-gray-400 w-5 text-center">{{ $banner->sort_order }}</span>
                                <p class="text-xs text-gray-700 flex-1 truncate">{{ $banner->title }}</p>
                                <a href="{{ route('admin.banners.edit', $banner) }}" class="text-xs text-blue-500 hover:underline">수정</a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-gray-400 text-center py-3">배너 없음</p>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endsection
