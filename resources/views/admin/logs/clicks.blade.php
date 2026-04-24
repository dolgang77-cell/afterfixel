@extends('admin.layouts.app')
@section('title', '클릭 로그')

@section('content')
<h1 class="text-2xl font-bold text-gray-800 mb-6">클릭 로그 (최근 30일)</h1>

{{-- 타입별 일별 통계 --}}
@forelse($clickStats as $type => $rows)
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <h2 class="font-semibold text-gray-700 mb-3">{{ $type }}</h2>
        <div class="overflow-x-auto">
            <div class="flex gap-1 items-end h-32">
                @foreach($rows->sortBy('date')->take(30) as $row)
                    @php $maxVal = $rows->max('total') ?: 1; @endphp
                    <div class="flex flex-col items-center flex-1 min-w-[20px]" title="{{ $row->date }}: {{ $row->total }}건">
                        <span class="text-[10px] text-gray-400 mb-1">{{ $row->total }}</span>
                        <div class="w-full bg-purple-400 rounded-t" style="height: {{ ($row->total / $maxVal) * 100 }}px"></div>
                        <span class="text-[9px] text-gray-400 mt-1 rotate-45 origin-left">{{ substr($row->date, 5) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@empty
    <div class="bg-white rounded-xl shadow-sm p-8 text-center text-gray-400 mb-6">클릭 로그가 없습니다.</div>
@endforelse

{{-- 최근 클릭 --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="px-4 py-3 border-b">
        <h2 class="font-semibold text-gray-700">최근 클릭 (50건)</h2>
    </div>
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
            <tr>
                <th class="px-4 py-3 text-left">시간</th>
                <th class="px-4 py-3 text-left">대상</th>
                <th class="px-4 py-3 text-left">출처</th>
                <th class="px-4 py-3 text-left">IP</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @foreach($recentClicks as $click)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 text-gray-500 text-xs">{{ $click->created_at->format('m-d H:i:s') }}</td>
                    <td class="px-4 py-2 text-gray-700">{{ $click->target_type }}#{{ $click->target_id }}</td>
                    <td class="px-4 py-2 text-gray-500">{{ $click->source ?? '-' }}</td>
                    <td class="px-4 py-2 text-gray-400 text-xs">{{ $click->ip_address }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
