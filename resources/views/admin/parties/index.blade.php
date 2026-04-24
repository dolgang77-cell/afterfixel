@extends('admin.layouts.app')
@section('title', '파티 관리')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">파티 관리</h1>
    <a href="{{ route('admin.parties.create') }}" class="px-4 py-2 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700">+ 파티 등록</a>
</div>

{{-- 필터 --}}
<form method="GET" class="bg-white rounded-xl shadow-sm p-4 mb-6 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs text-gray-500 mb-1">검색</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="파티명" class="px-3 py-1.5 border rounded-lg text-sm w-40">
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">클럽</label>
        <select name="club_id" class="px-3 py-1.5 border rounded-lg text-sm">
            <option value="">전체</option>
            @foreach($clubs as $id => $name)
                <option value="{{ $id }}" {{ request('club_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">상태</label>
        <select name="status" class="px-3 py-1.5 border rounded-lg text-sm">
            <option value="">전체</option>
            <option value="upcoming" {{ request('status') === 'upcoming' ? 'selected' : '' }}>예정</option>
            <option value="ongoing" {{ request('status') === 'ongoing' ? 'selected' : '' }}>진행중</option>
            <option value="ended" {{ request('status') === 'ended' ? 'selected' : '' }}>종료</option>
            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>취소</option>
        </select>
    </div>
    <button type="submit" class="px-4 py-1.5 bg-gray-800 text-white text-sm rounded-lg">검색</button>
    <a href="{{ route('admin.parties.index') }}" class="px-4 py-1.5 bg-gray-200 text-gray-600 text-sm rounded-lg">초기화</a>
</form>

{{-- 일괄 상태 변경 --}}
<div x-data="partyBulk()">
    <form method="POST" action="{{ route('admin.parties.bulk-status') }}" id="bulkStatusForm">
        @csrf
        <div x-show="selected.length > 0" x-cloak
             class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg flex items-center gap-3 text-sm">
            <span class="text-blue-700 font-medium" x-text="selected.length + '개 선택됨'"></span>
            <select name="status" class="px-2 py-1 border rounded text-xs">
                <option value="ended">종료로 변경</option>
                <option value="cancelled">취소로 변경</option>
                <option value="upcoming">예정으로 변경</option>
            </select>
            <button type="submit" class="px-3 py-1 bg-blue-500 text-white rounded-lg text-xs font-medium hover:bg-blue-600">일괄 변경</button>
        </div>

        {{-- 테이블 --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-3 py-3 text-center w-10">
                            <input type="checkbox" @click="toggleAll($event)" class="rounded border-gray-300">
                        </th>
                        <th class="px-4 py-3 text-left">ID</th>
                        <th class="px-4 py-3 text-left">파티명</th>
                        <th class="px-4 py-3 text-left">클럽</th>
                        <th class="px-4 py-3 text-left">날짜</th>
                        <th class="px-4 py-3 text-left">시간</th>
                        <th class="px-4 py-3 text-left">장르</th>
                        <th class="px-4 py-3 text-left">구분</th>
                        <th class="px-4 py-3 text-center">상태</th>
                        <th class="px-4 py-3 text-center">조회수</th>
                        <th class="px-4 py-3 text-right">관리</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($parties as $party)
                        @php
                            $statusColors = [
                                'upcoming'  => 'bg-blue-100 text-blue-700',
                                'ongoing'   => 'bg-green-100 text-green-700',
                                'ended'     => 'bg-gray-100 text-gray-500',
                                'cancelled' => 'bg-red-100 text-red-600',
                            ];
                            $statusLabels = [
                                'upcoming'  => '예정',
                                'ongoing'   => '진행중',
                                'ended'     => '종료',
                                'cancelled' => '취소',
                            ];
                            $eventTypeColors = [
                                'verified_event' => 'bg-emerald-100 text-emerald-700',
                                'operating_card' => 'bg-cyan-100 text-cyan-700',
                                'general_event' => 'bg-gray-100 text-gray-600',
                            ];
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-3 text-center">
                                <input type="checkbox" name="ids[]" value="{{ $party->id }}"
                                       @click="toggle({{ $party->id }})" :checked="selected.includes({{ $party->id }})"
                                       class="rounded border-gray-300">
                            </td>
                            <td class="px-4 py-3 text-gray-400">{{ $party->id }}</td>
                            <td class="px-4 py-3 font-medium text-gray-800">{{ $party->name }}</td>
                            <td class="px-4 py-3 text-gray-600 text-xs">{{ $party->club?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $party->event_date?->format('m/d') }}</td>
                            <td class="px-4 py-3 text-gray-500 text-xs">{{ $party->time_range_text }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $party->genre ?: '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="space-y-1">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs {{ $eventTypeColors[$party->event_card_type] ?? $eventTypeColors['general_event'] }}">
                                        {{ $party->event_card_label }}
                                    </span>
                                    <p class="max-w-[240px] text-[11px] leading-4 text-gray-500">{{ $party->event_card_notice }}</p>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-xs {{ $statusColors[$party->status] ?? '' }}">
                                    {{ $statusLabels[$party->status] ?? $party->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-gray-500">{{ number_format($party->view_count) }}</td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <a href="{{ route('admin.parties.edit', $party) }}" class="text-blue-600 hover:underline text-xs">수정</a>
                                <form action="{{ route('admin.parties.destroy', $party) }}" method="POST" class="inline" onsubmit="return confirm('정말 삭제하시겠습니까?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:underline text-xs">삭제</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="11" class="px-4 py-8 text-center text-gray-400">등록된 파티가 없습니다.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </form>
</div>

<div class="mt-4">{{ $parties->links() }}</div>

<script>
function partyBulk() {
    return {
        selected: [],
        toggle(id) {
            const idx = this.selected.indexOf(id);
            idx === -1 ? this.selected.push(id) : this.selected.splice(idx, 1);
        },
        toggleAll(e) {
            if (e.target.checked) {
                this.selected = @json($parties->pluck('id'));
            } else {
                this.selected = [];
            }
        },
    };
}
</script>
@endsection
