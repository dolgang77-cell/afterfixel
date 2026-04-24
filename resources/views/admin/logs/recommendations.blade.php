@extends('admin.layouts.app')
@section('title', '추천 로그')

@section('content')
<h1 class="text-2xl font-bold text-gray-800 mb-6">추천 로그</h1>

{{-- 일별 통계 --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <h2 class="font-semibold text-gray-700 mb-3">일별 추천 횟수 (최근 30일)</h2>
    @if($dailyStats->count())
        <div class="overflow-x-auto">
            <div class="flex gap-1 items-end h-32">
                @foreach($dailyStats->sortBy('date') as $row)
                    @php $maxVal = $dailyStats->max('total') ?: 1; @endphp
                    <div class="flex flex-col items-center flex-1 min-w-[20px]" title="{{ $row->date }}: {{ $row->total }}건">
                        <span class="text-[10px] text-gray-400 mb-1">{{ $row->total }}</span>
                        <div class="w-full bg-pink-400 rounded-t" style="height: {{ ($row->total / $maxVal) * 100 }}px"></div>
                        <span class="text-[9px] text-gray-400 mt-1 rotate-45 origin-left">{{ substr($row->date, 5) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <p class="text-sm text-gray-400">데이터가 없습니다.</p>
    @endif
</div>

{{-- 추천 목록 --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
            <tr>
                <th class="px-4 py-3 text-left">ID</th>
                <th class="px-4 py-3 text-left">입력 조건</th>
                <th class="px-4 py-3 text-center">코스 수</th>
                <th class="px-4 py-3 text-center">예상 비용</th>
                <th class="px-4 py-3 text-left">시간</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($recommendations as $rec)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-400">{{ $rec->id }}</td>
                    <td class="px-4 py-3 text-gray-700 text-xs">
                        @if($rec->input_condition)
                            {{ $rec->input_condition['area'] ?? '' }}
                            / {{ $rec->input_condition['genre'] ?? '전체' }}
                            / {{ number_format($rec->input_condition['budget'] ?? 0) }}원
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center text-gray-600">{{ is_array($rec->ordered_stops) ? count($rec->ordered_stops) : 0 }}</td>
                    <td class="px-4 py-3 text-center text-gray-600">{{ number_format($rec->total_cost ?? 0) }}원</td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $rec->created_at->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">추천 로그가 없습니다.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $recommendations->links() }}</div>
@endsection
