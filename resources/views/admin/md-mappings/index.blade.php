@extends('admin.layouts.app')
@section('title', 'MD 매칭 관리')

@section('content')
<h1 class="text-2xl font-bold text-gray-800 mb-6">MD 매칭 관리</h1>

{{-- 매칭 추가 폼 --}}
<div class="bg-white rounded-xl shadow-sm p-5 mb-6" x-data="{ type: 'club' }">
    <h3 class="text-sm font-semibold text-gray-600 mb-4">새 매칭 추가</h3>
    <form method="POST" action="{{ route('admin.md-mappings.store') }}" class="flex flex-wrap gap-3 items-end">
        @csrf
        <div>
            <label class="block text-xs text-gray-500 mb-1">MD</label>
            <select name="md_profile_id" required class="px-3 py-1.5 border rounded-lg text-sm">
                <option value="">선택</option>
                @foreach($mds as $md)
                    <option value="{{ $md->id }}">{{ $md->display_name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">유형</label>
            <select name="type" x-model="type" class="px-3 py-1.5 border rounded-lg text-sm">
                <option value="club">클럽</option>
                <option value="party">파티</option>
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">대상</label>
            <select name="target_id" required class="px-3 py-1.5 border rounded-lg text-sm w-48">
                <template x-if="type === 'club'">
                    <optgroup label="클럽">
                        <option value="">선택</option>
                        @foreach($clubs as $club)
                            <option value="{{ $club->id }}">{{ $club->name }} ({{ $club->area }})</option>
                        @endforeach
                    </optgroup>
                </template>
                <template x-if="type === 'party'">
                    <optgroup label="파티">
                        <option value="">선택</option>
                        @foreach($parties as $party)
                            <option value="{{ $party->id }}">{{ $party->name }} ({{ $party->event_date?->format('n/j') }})</option>
                        @endforeach
                    </optgroup>
                </template>
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">순서</label>
            <input type="number" name="priority" value="0" min="0" class="px-3 py-1.5 border rounded-lg text-sm w-16">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">메모</label>
            <input type="text" name="note" class="px-3 py-1.5 border rounded-lg text-sm w-32" placeholder="선택">
        </div>
        <div class="flex items-center gap-2 pb-0.5">
            <input type="hidden" name="visible" value="0">
            <input type="checkbox" name="visible" value="1" checked class="rounded border-gray-300 text-purple-600">
            <span class="text-xs text-gray-500">노출</span>
        </div>
        <button type="submit" class="px-4 py-1.5 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700">추가</button>
    </form>
</div>

{{-- 클럽 매칭 목록 --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
    <div class="px-4 py-3 bg-gray-50 border-b"><h3 class="text-sm font-semibold text-gray-600">클럽 매칭 ({{ $clubMappings->count() }})</h3></div>
    <table class="w-full text-sm">
        <thead class="text-gray-400 text-xs uppercase"><tr>
            <th class="px-4 py-2 text-left">MD</th><th class="text-left">클럽</th><th class="text-left">지역</th><th class="text-center">노출</th><th class="text-center">순서</th><th class="text-left">메모</th><th class="text-right">관리</th>
        </tr></thead>
        <tbody class="divide-y">
            @forelse($clubMappings as $m)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-2 font-medium text-gray-800">{{ $m->md_name }}</td>
                <td class="text-gray-600">{{ $m->club_name }}</td>
                <td class="text-gray-500 text-xs">{{ $m->area }}</td>
                <td class="text-center"><span class="text-xs {{ $m->visible ? 'text-green-600' : 'text-gray-400' }}">{{ $m->visible ? 'ON' : 'OFF' }}</span></td>
                <td class="text-center text-gray-500">{{ $m->priority }}</td>
                <td class="text-gray-500 text-xs max-w-[120px] truncate">{{ $m->note ?? '-' }}</td>
                <td class="text-right">
                    <form action="{{ route('admin.md-mappings.destroy', ['type' => 'club', 'id' => $m->id]) }}" method="POST" class="inline" onsubmit="return confirm('삭제하시겠습니까?')">
                        @csrf @method('DELETE')
                        <button class="text-xs px-2 py-1 rounded bg-red-100 text-red-600 hover:bg-red-200">삭제</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-4 py-4 text-center text-gray-400">클럽 매칭이 없습니다.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- 파티 매칭 목록 --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="px-4 py-3 bg-gray-50 border-b"><h3 class="text-sm font-semibold text-gray-600">파티 매칭 ({{ $partyMappings->count() }})</h3></div>
    <table class="w-full text-sm">
        <thead class="text-gray-400 text-xs uppercase"><tr>
            <th class="px-4 py-2 text-left">MD</th><th class="text-left">파티</th><th class="text-left">날짜</th><th class="text-center">노출</th><th class="text-center">순서</th><th class="text-left">메모</th><th class="text-right">관리</th>
        </tr></thead>
        <tbody class="divide-y">
            @forelse($partyMappings as $m)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-2 font-medium text-gray-800">{{ $m->md_name }}</td>
                <td class="text-gray-600">{{ $m->party_name }}</td>
                <td class="text-gray-500 text-xs">{{ $m->event_date }}</td>
                <td class="text-center"><span class="text-xs {{ $m->visible ? 'text-green-600' : 'text-gray-400' }}">{{ $m->visible ? 'ON' : 'OFF' }}</span></td>
                <td class="text-center text-gray-500">{{ $m->priority }}</td>
                <td class="text-gray-500 text-xs max-w-[120px] truncate">{{ $m->note ?? '-' }}</td>
                <td class="text-right">
                    <form action="{{ route('admin.md-mappings.destroy', ['type' => 'party', 'id' => $m->id]) }}" method="POST" class="inline" onsubmit="return confirm('삭제하시겠습니까?')">
                        @csrf @method('DELETE')
                        <button class="text-xs px-2 py-1 rounded bg-red-100 text-red-600 hover:bg-red-200">삭제</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-4 py-4 text-center text-gray-400">파티 매칭이 없습니다.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
