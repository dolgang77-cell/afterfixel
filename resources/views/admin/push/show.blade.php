@extends('admin.layouts.app')
@section('title', '푸쉬 상세')
@section('content')
<div class="max-w-4xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.push.index') }}" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg></a>
        <h1 class="text-2xl font-bold text-gray-800">푸쉬 상세 #{{ $campaign->id }}</h1>
    </div>

    <div class="grid md:grid-cols-3 gap-6">
        <div class="md:col-span-2 bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-2">{{ $campaign->title }}</h2>
            <p class="text-sm text-gray-600 mb-4 whitespace-pre-wrap">{{ $campaign->body }}</p>
            @if($campaign->image)<img src="{{ $campaign->image }}" class="w-full h-40 object-cover rounded-lg mb-4">@endif
            @if($campaign->link)<p class="text-sm text-gray-500">링크: <a href="{{ $campaign->link }}" class="text-purple-600 hover:underline">{{ $campaign->link }}</a></p>@endif

            <div class="grid grid-cols-2 gap-4 text-sm mt-6 border-t pt-4">
                <div><span class="text-gray-400">유형:</span> {{ \App\Models\PushCampaign::$types[$campaign->campaign_type] }}</div>
                <div><span class="text-gray-400">발송 방식:</span> {{ $campaign->send_type === 'immediate' ? '즉시' : '예약' }}</div>
                <div><span class="text-gray-400">생성자:</span> {{ $campaign->creator?->name }}</div>
                <div><span class="text-gray-400">생성일:</span> {{ $campaign->created_at->format('Y-m-d H:i') }}</div>
                @if($campaign->scheduled_at)<div><span class="text-gray-400">예약 시각:</span> {{ $campaign->scheduled_at->format('Y-m-d H:i') }}</div>@endif
                @if($campaign->sent_at)<div><span class="text-gray-400">발송 시각:</span> {{ $campaign->sent_at->format('Y-m-d H:i') }}</div>@endif
            </div>

            <div class="mt-6 rounded-xl border border-gray-100 bg-gray-50 px-4 py-4 text-sm">
                <p class="font-semibold text-gray-700">타겟 요약</p>
                <div class="mt-3 grid grid-cols-2 gap-3">
                    <div><span class="text-gray-400">대상:</span> {{ $targetSummary['targetType'] }}</div>
                    @if($targetSummary['retentionPreset'])
                        <div><span class="text-gray-400">리텐션:</span> {{ $targetSummary['retentionPreset'] }}</div>
                    @endif
                    @if($targetSummary['retentionDays'])
                        <div><span class="text-gray-400">조회 기간:</span> 최근 {{ $targetSummary['retentionDays'] }}일</div>
                    @endif
                    <div><span class="text-gray-400">운영 계정 제외:</span> {{ $targetSummary['excludeStaff'] ? '예' : '아니오' }}</div>
                    @if(!empty($targetSummary['areas']))
                        <div class="col-span-2"><span class="text-gray-400">지역:</span> {{ implode(', ', $targetSummary['areas']) }}</div>
                    @endif
                    @if(!empty($targetSummary['genres']))
                        <div class="col-span-2"><span class="text-gray-400">장르:</span> {{ implode(', ', $targetSummary['genres']) }}</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="space-y-4">
            {{-- 상태 --}}
            <div class="bg-white rounded-xl shadow-sm p-5">
                @php $sc=['draft'=>'bg-gray-200 text-gray-600','scheduled'=>'bg-blue-100 text-blue-700','sending'=>'bg-yellow-100 text-yellow-700','sent'=>'bg-green-100 text-green-700','failed'=>'bg-red-100 text-red-700','cancelled'=>'bg-gray-200 text-gray-400']; @endphp
                <p class="text-sm font-semibold text-gray-600 mb-2">상태</p>
                <span class="px-3 py-1 rounded-full text-sm font-medium {{ $sc[$campaign->status] ?? '' }}">{{ \App\Models\PushCampaign::$statuses[$campaign->status] }}</span>

                @if($campaign->status === 'scheduled')
                <div class="mt-4 flex gap-2">
                    <form action="{{ route('admin.push.send-now', $campaign) }}" method="POST" onsubmit="return confirm('지금 즉시 발송하시겠습니까?')">@csrf
                        <button class="px-3 py-1.5 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700">즉시 발송</button>
                    </form>
                    <form action="{{ route('admin.push.cancel', $campaign) }}" method="POST" onsubmit="return confirm('예약을 취소하시겠습니까?')">@csrf @method('PATCH')
                        <button class="px-3 py-1.5 bg-gray-500 text-white text-sm rounded-lg hover:bg-gray-600">취소</button>
                    </form>
                </div>
                @endif
            </div>

            {{-- 통계 --}}
            <div class="bg-white rounded-xl shadow-sm p-5">
                <p class="text-sm font-semibold text-gray-600 mb-3">발송 통계</p>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">대상</span><span class="font-medium">{{ number_format($campaign->target_count) }}명</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">성공</span><span class="font-medium text-green-600">{{ number_format($campaign->sent_count) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">실패</span><span class="font-medium text-red-600">{{ number_format($campaign->failed_count) }}</span></div>
                    <div class="flex justify-between border-t pt-2"><span class="text-gray-500">클릭</span><span class="font-medium text-purple-600">{{ number_format($campaign->clicked_count) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">유입</span><span class="font-medium text-blue-600">{{ number_format($campaign->inflow_count) }}</span></div>
                    @if($campaign->sent_count > 0)
                    <div class="flex justify-between border-t pt-2"><span class="text-gray-500">클릭률</span><span class="font-medium">{{ round($campaign->clicked_count / $campaign->sent_count * 100, 1) }}%</span></div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
