@extends('admin.layouts.app')
@section('title', 'MD 상세')

@section('content')
<div class="max-w-4xl">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.md.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            </a>
            <h1 class="text-2xl font-bold text-gray-800">MD 상세</h1>
        </div>
        <a href="{{ route('admin.md.edit', $md) }}" class="px-4 py-2 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700">수정</a>
    </div>

    <div class="grid md:grid-cols-3 gap-6">
        <div class="md:col-span-2 bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center gap-4 mb-6">
                @if($md->profile_image)
                    @php $profileSrcset = $md->profile_image_srcset; @endphp
                    <img src="{{ $md->profile_image }}" class="w-16 h-16 rounded-full object-cover" decoding="async"
                         @if($profileSrcset) srcset="{{ $profileSrcset }}" sizes="64px" @endif>
                @else
                    <div class="w-16 h-16 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 text-xl font-bold">{{ mb_substr($md->display_name, 0, 1) }}</div>
                @endif
                <div>
                    <h2 class="text-lg font-bold text-gray-800">{{ $md->display_name }}</h2>
                    <p class="text-sm text-gray-500">{{ $md->affiliation ?? '소속 없음' }}</p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 text-sm mb-4">
                <div><p class="text-xs text-gray-400">지역</p><p class="text-gray-700">{{ $md->areas ? implode(', ', $md->areas) : '-' }}</p></div>
                <div><p class="text-xs text-gray-400">장르</p><p class="text-gray-700">{{ $md->genres ? implode(', ', $md->genres) : '-' }}</p></div>
                <div><p class="text-xs text-gray-400">연결 계정</p><p class="text-gray-700">{{ $md->user?->email ?? '미연결' }}</p></div>
                <div><p class="text-xs text-gray-400">외부 링크</p><p class="text-gray-700">{{ $md->external_link ?? '-' }}</p></div>
            </div>
            @if($md->intro)
            <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-700 whitespace-pre-wrap">{{ $md->intro }}</div>
            @endif
            @if($md->admin_memo)
            <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg text-sm text-yellow-800">
                <p class="text-xs font-semibold text-yellow-600 mb-1">관리자 메모</p>
                {{ $md->admin_memo }}
            </div>
            @endif
        </div>

        <div class="space-y-4">
            <div class="bg-white rounded-xl shadow-sm p-5 text-sm">
                <p class="text-xs text-gray-400 mb-1">상태</p>
                @php $sc = ['active' => 'bg-green-100 text-green-700', 'inactive' => 'bg-gray-200 text-gray-500', 'hidden' => 'bg-yellow-100 text-yellow-700']; @endphp
                <span class="px-2 py-0.5 rounded-full text-xs {{ $sc[$md->status] ?? '' }}">{{ ['active'=>'활동중','inactive'=>'비활동','hidden'=>'숨김'][$md->status] }}</span>
                <p class="text-xs text-gray-400 mt-3 mb-1">노출</p>
                <span class="{{ $md->visible ? 'text-green-600' : 'text-gray-400' }}">{{ $md->visible ? 'ON' : 'OFF' }}</span>
                <p class="text-xs text-gray-400 mt-3 mb-1">우선순위</p>
                <span>{{ $md->priority }}</span>
            </div>
        </div>
    </div>

    {{-- 담당 클럽 --}}
    <div class="bg-white rounded-xl shadow-sm p-6 mt-6">
        <h3 class="text-sm font-semibold text-gray-600 mb-3">담당 클럽 ({{ $md->clubs->count() }})</h3>
        @if($md->clubs->isNotEmpty())
        <div class="space-y-2">
            @foreach($md->clubs as $club)
            <div class="flex items-center justify-between text-sm p-2 rounded-lg hover:bg-gray-50">
                <span class="text-gray-800">{{ $club->name }} <span class="text-gray-400 text-xs">({{ $club->area }})</span></span>
                <span class="text-xs {{ $club->pivot->visible ? 'text-green-600' : 'text-gray-400' }}">{{ $club->pivot->visible ? '노출' : '비노출' }}</span>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-sm text-gray-400">담당 클럽이 없습니다.</p>
        @endif
    </div>

    {{-- 담당 파티 --}}
    <div class="bg-white rounded-xl shadow-sm p-6 mt-4">
        <h3 class="text-sm font-semibold text-gray-600 mb-3">담당 파티 ({{ $md->parties->count() }})</h3>
        @if($md->parties->isNotEmpty())
        <div class="space-y-2">
            @foreach($md->parties as $party)
            <div class="flex items-center justify-between text-sm p-2 rounded-lg hover:bg-gray-50">
                <span class="text-gray-800">{{ $party->name }} <span class="text-gray-400 text-xs">({{ $party->event_date?->format('n/j') }})</span></span>
                <span class="text-xs {{ $party->pivot->visible ? 'text-green-600' : 'text-gray-400' }}">{{ $party->pivot->visible ? '노출' : '비노출' }}</span>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-sm text-gray-400">담당 파티가 없습니다.</p>
        @endif
    </div>
</div>
@endsection
