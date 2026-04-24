@extends('admin.layouts.app')
@section('title', $campaign->exists ? '푸쉬 수정' : '새 푸쉬 발송')
@section('content')
<div class="max-w-3xl">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">{{ $campaign->exists ? '푸쉬 수정' : '새 푸쉬 발송' }}</h1>

    <form method="POST"
          action="{{ $campaign->exists ? route('admin.push.update', $campaign) : route('admin.push.store') }}"
          class="bg-white rounded-xl shadow-sm p-6 space-y-5" x-data="{ sendType: '{{ old('send_type', $campaign->send_type ?? 'immediate') }}', targetType: '{{ old('target_type', $campaign->target_type ?? 'all') }}' }">
        @csrf
        @if($campaign->exists) @method('PUT') @endif

        @if(!$campaign->exists)
        <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-4">
            <p class="text-sm font-semibold text-gray-700">리텐션 프리셋 빠른 시작</p>
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach($retentionPresets as $presetValue => $presetLabel)
                    <a href="{{ route('admin.push.create', ['preset' => $presetValue]) }}" class="rounded-full px-3 py-2 text-[12px] font-semibold {{ request('preset') === $presetValue ? 'bg-purple-600 text-white' : 'bg-white text-gray-600 border border-gray-200' }}">{{ $presetLabel }}</a>
                @endforeach
            </div>
        </div>
        @endif

        <div>
            <label class="block text-sm text-gray-600 mb-1">제목 <span class="text-red-500">*</span></label>
            <input type="text" name="title" value="{{ old('title', $campaign->title) }}" required class="w-full px-3 py-2 border rounded-lg text-sm">
        </div>

        <div>
            <label class="block text-sm text-gray-600 mb-1">본문 <span class="text-red-500">*</span></label>
            <textarea name="body" rows="4" required class="w-full px-3 py-2 border rounded-lg text-sm">{{ old('body', $campaign->body) }}</textarea>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div x-data="pushImageUploader('{{ old('image', $campaign->image) }}')">
                <label class="block text-sm text-gray-600 mb-1">이미지</label>
                <input type="hidden" name="image" x-model="url">
                <div class="flex gap-3 items-start">
                    <div class="flex-1 space-y-2">
                        <input type="text" x-model="url" class="w-full px-3 py-2 border rounded-lg text-sm" placeholder="URL 입력 또는 이미지 업로드">
                        <label class="inline-flex items-center gap-2 px-3 py-1.5 bg-purple-50 text-purple-700 rounded-lg text-sm font-medium cursor-pointer hover:bg-purple-100">
                            <span x-text="uploading ? '업로드 중...' : '파일 선택'"></span>
                            <input type="file" accept="image/*" x-on:change="upload($event)" class="hidden" :disabled="uploading">
                        </label>
                    </div>
                    <div class="shrink-0 w-24 h-16 border rounded-lg overflow-hidden bg-gray-50 flex items-center justify-center" x-show="url">
                        <img :src="url" class="w-full h-full object-cover">
                    </div>
                </div>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">클릭 링크</label>
                <input type="text" name="link" value="{{ old('link', $campaign->link) }}" class="w-full px-3 py-2 border rounded-lg text-sm" placeholder="/parties/1">
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-600 mb-1">유형</label>
                <select name="campaign_type" class="w-full px-3 py-2 border rounded-lg text-sm">
                    @foreach(\App\Models\PushCampaign::$types as $v=>$l)
                        <option value="{{ $v }}" {{ old('campaign_type', $campaign->campaign_type ?? 'notice')===$v?'selected':'' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">발송 방식</label>
                <select name="send_type" x-model="sendType" class="w-full px-3 py-2 border rounded-lg text-sm">
                    <option value="immediate">즉시 발송</option>
                    <option value="scheduled">예약 발송</option>
                </select>
            </div>
        </div>

        <div x-show="sendType === 'scheduled'">
            <label class="block text-sm text-gray-600 mb-1">예약 시각</label>
            <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at', $campaign->scheduled_at?->format('Y-m-d\TH:i')) }}" class="w-full px-3 py-2 border rounded-lg text-sm">
        </div>

        <fieldset class="border rounded-lg p-4">
            <legend class="text-sm font-semibold text-gray-600 px-2">타겟 설정</legend>
            <div class="space-y-3">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">대상</label>
                    <select name="target_type" x-model="targetType" class="w-full px-3 py-2 border rounded-lg text-sm">
                        <option value="all">전체 사용자</option>
                        <option value="logged_in">로그인 사용자</option>
                        <option value="area">특정 지역 선호</option>
                        <option value="genre">특정 장르 선호</option>
                        <option value="custom">리텐션 프리셋</option>
                    </select>
                </div>
                <div x-show="targetType === 'area'">
                    <label class="block text-sm text-gray-600 mb-1">지역</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($areas as $a)
                        <label class="flex items-center gap-1"><input type="checkbox" name="target_areas[]" value="{{ $a }}" class="rounded border-gray-300 text-purple-600" {{ in_array($a, old('target_areas', data_get($campaign->target_query, 'areas', [])), true) ? 'checked' : '' }}><span class="text-sm">{{ $a }}</span></label>
                        @endforeach
                    </div>
                </div>
                <div x-show="targetType === 'genre'">
                    <label class="block text-sm text-gray-600 mb-1">장르</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($genres as $g)
                        <label class="flex items-center gap-1"><input type="checkbox" name="target_genres[]" value="{{ $g }}" class="rounded border-gray-300 text-purple-600" {{ in_array($g, old('target_genres', data_get($campaign->target_query, 'genres', [])), true) ? 'checked' : '' }}><span class="text-sm">{{ $g }}</span></label>
                        @endforeach
                    </div>
                </div>
                <div x-show="targetType === 'custom'" class="space-y-3 rounded-xl border border-amber-100 bg-amber-50/70 px-4 py-4">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">리텐션 조건</label>
                        <select name="retention_preset" class="w-full px-3 py-2 border rounded-lg text-sm">
                            <option value="">선택하세요</option>
                            @foreach($retentionPresets as $presetValue => $presetLabel)
                                <option value="{{ $presetValue }}" {{ old('retention_preset', data_get($campaign->target_query, 'retention_preset')) === $presetValue ? 'selected' : '' }}>{{ $presetLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">조회 기간 (일)</label>
                        <input type="number" min="1" max="30" name="retention_days" value="{{ old('retention_days', data_get($campaign->target_query, 'retention_days', 7)) }}" class="w-full px-3 py-2 border rounded-lg text-sm">
                        <p class="mt-1 text-[11px] text-gray-500">최근 N일 안의 최근 본/찜/미확인 답변 데이터를 기준으로 발송합니다.</p>
                    </div>
                    <p class="text-[11px] text-amber-700">리텐션 프리셋은 사용자별 원래 상세/문의 화면 링크를 우선 사용합니다. 상단의 클릭 링크는 개별 대상이 없을 때만 fallback 으로 사용됩니다.</p>
                </div>
                <label class="flex items-center gap-2"><input type="checkbox" name="exclude_staff" value="1" {{ old('exclude_staff', data_get($campaign->target_query, 'exclude_staff', true)) ? 'checked' : '' }} class="rounded border-gray-300 text-purple-600"><span class="text-sm text-gray-600">MD/관리자 제외</span></label>
            </div>
        </fieldset>

        <div class="flex items-center gap-3 pt-4 border-t">
            <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 text-sm font-medium" onclick="return confirm(sendType === 'immediate' ? '즉시 발송하시겠습니까?' : '예약하시겠습니까?')">
                <span x-text="sendType === 'immediate' ? '즉시 발송' : '예약하기'"></span>
            </button>
            <a href="{{ route('admin.push.index') }}" class="px-6 py-2 bg-gray-200 text-gray-600 rounded-lg text-sm">취소</a>
        </div>
    </form>
</div>
@push('scripts')
<script>
function pushImageUploader(initialUrl) {
    return {
        url: initialUrl || '', uploading: false,
        async upload(e) {
            const file = e.target.files[0]; if (!file) return;
            this.uploading = true;
            const fd = new FormData(); fd.append('image', file);
            try {
                const res = await fetch('{{ route("admin.upload-image") }}', { method:'POST', body:fd, headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Accept':'application/json'} });
                const data = await res.json(); if(data.url) this.url = data.url;
            } catch {}
            this.uploading = false; e.target.value = '';
        }
    };
}
</script>
@endpush
@endsection
