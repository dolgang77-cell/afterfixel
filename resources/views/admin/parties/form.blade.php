@extends('admin.layouts.app')
@section('title', $party->exists ? '파티 수정' : '파티 등록')

@section('content')
<div class="max-w-3xl">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">{{ $party->exists ? '파티 수정' : '파티 등록' }}</h1>

    <form method="POST"
          action="{{ $party->exists ? route('admin.parties.update', $party) : route('admin.parties.store') }}"
          class="bg-white rounded-xl shadow-sm p-6 space-y-6">
        @csrf
        @if($party->exists) @method('PUT') @endif

        <fieldset class="space-y-4">
            <legend class="text-sm font-semibold text-gray-600 uppercase tracking-wider">기본 정보</legend>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">파티명 <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $party->name) }}" required class="w-full px-3 py-2 border rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">클럽 <span class="text-red-500">*</span></label>
                    <select name="club_id" required class="w-full px-3 py-2 border rounded-lg text-sm">
                        <option value="">선택</option>
                        @foreach($clubs as $id => $name)
                            <option value="{{ $id }}" {{ old('club_id', $party->club_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">날짜 <span class="text-red-500">*</span></label>
                    <input type="date" name="event_date" value="{{ old('event_date', $party->event_date?->format('Y-m-d')) }}" required class="w-full px-3 py-2 border rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">시작 시간</label>
                    <input type="time" name="start_time" value="{{ old('start_time', $party->start_time) }}" class="w-full px-3 py-2 border rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">종료 시간</label>
                    <input type="time" name="end_time" value="{{ old('end_time', $party->end_time) }}" class="w-full px-3 py-2 border rounded-lg text-sm">
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">장르</label>
                    <select name="genre" class="w-full px-3 py-2 border rounded-lg text-sm">
                        <option value="">선택</option>
                        @foreach(\App\Models\Club::$genres as $genre)
                            <option value="{{ $genre }}" {{ old('genre', $party->genre) === $genre ? 'selected' : '' }}>{{ $genre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">상태 <span class="text-red-500">*</span></label>
                    <select name="status" required class="w-full px-3 py-2 border rounded-lg text-sm">
                        @foreach(['upcoming' => '예정', 'ongoing' => '진행중', 'ended' => '종료', 'cancelled' => '취소'] as $val => $label)
                            <option value="{{ $val }}" {{ old('status', $party->status ?? 'upcoming') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">라인업</label>
                <input type="text" name="lineup" value="{{ old('lineup', $party->lineup) }}" placeholder="DJ A, DJ B" class="w-full px-3 py-2 border rounded-lg text-sm">
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">설명</label>
                <textarea name="description" rows="3" class="w-full px-3 py-2 border rounded-lg text-sm">{{ old('description', $party->description) }}</textarea>
            </div>
        </fieldset>

        <fieldset class="space-y-4">
            <legend class="text-sm font-semibold text-gray-600 uppercase tracking-wider">가격 / 조건</legend>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">티켓 최소가 (원)</label>
                    <input type="number" name="ticket_price_min" value="{{ old('ticket_price_min', $party->ticket_price_min) }}" min="0" step="1000" class="w-full px-3 py-2 border rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">티켓 최대가 (원)</label>
                    <input type="number" name="ticket_price_max" value="{{ old('ticket_price_max', $party->ticket_price_max) }}" min="0" step="1000" class="w-full px-3 py-2 border rounded-lg text-sm">
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">드레스코드</label>
                    <input type="text" name="dress_code" value="{{ old('dress_code', $party->dress_code) }}" class="w-full px-3 py-2 border rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">입장 조건</label>
                    <input type="text" name="entry_condition" value="{{ old('entry_condition', $party->entry_condition) }}" class="w-full px-3 py-2 border rounded-lg text-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">예매 링크</label>
                <input type="url" name="booking_link" value="{{ old('booking_link', $party->booking_link) }}" class="w-full px-3 py-2 border rounded-lg text-sm">
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">운영 가이드 / FAQ 메모</label>
                <textarea name="guide_text" rows="3" class="w-full px-3 py-2 border rounded-lg text-sm" placeholder="예: 오픈 직후 입장 원활, 티켓 소진 시 현장 구매 제한, 라인업 변동 가능">{{ old('guide_text', $party->guide_text) }}</textarea>
                <p class="mt-1 text-xs text-gray-500">상세 페이지의 파티 FAQ 바텀시트와 운영 가이드 영역에 함께 노출됩니다.</p>
            </div>
        </fieldset>

        <fieldset class="space-y-4">
            <legend class="text-sm font-semibold text-gray-600 uppercase tracking-wider">미디어</legend>

            <div x-data="imageUploader('{{ old('thumbnail', $party->thumbnail) }}')">
                <label class="block text-sm text-gray-600 mb-1">썸네일</label>
                <div class="flex gap-3 items-start">
                    <div class="flex-1 space-y-2">
                        <input type="url" name="thumbnail" x-model="url" class="w-full px-3 py-2 border rounded-lg text-sm" placeholder="URL 입력 또는 이미지 업로드">
                        <label class="inline-flex items-center gap-2 px-4 py-2 bg-purple-50 text-purple-700 rounded-lg text-sm font-medium cursor-pointer hover:bg-purple-100 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                            <span x-text="uploading ? '업로드 중...' : '파일 선택'"></span>
                            <input type="file" accept="image/*" @change="upload($event)" class="hidden" :disabled="uploading">
                        </label>
                        <p x-show="error" x-text="error" class="text-xs text-red-500"></p>
                    </div>
                    <div class="shrink-0 w-24 h-24 border rounded-lg overflow-hidden bg-gray-50 flex items-center justify-center">
                        <template x-if="url">
                            <img :src="url" class="w-full h-full object-cover" x-on:error="$el.style.display='none'">
                        </template>
                        <template x-if="!url">
                            <span class="text-xs text-gray-400">미리보기</span>
                        </template>
                    </div>
                </div>
            </div>
            <div x-data="multiImageUploader({{ json_encode(old('images_text') ? explode("\n", old('images_text')) : ($party->images ?? [])) }})">
                <label class="block text-sm text-gray-600 mb-1">이미지 (여러 장)</label>
                <textarea name="images_text" rows="3" x-model="urlsText" class="w-full px-3 py-2 border rounded-lg text-sm" placeholder="URL 입력 또는 이미지 업로드 (줄바꿈 구분)"></textarea>
                <div class="mt-2 flex items-center gap-2">
                    <label class="inline-flex items-center gap-2 px-4 py-2 bg-purple-50 text-purple-700 rounded-lg text-sm font-medium cursor-pointer hover:bg-purple-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                        <span x-text="uploading ? '업로드 중...' : '이미지 추가'"></span>
                        <input type="file" accept="image/*" multiple @change="uploadMulti($event)" class="hidden" :disabled="uploading">
                    </label>
                    <p x-show="error" x-text="error" class="text-xs text-red-500"></p>
                </div>
                <div class="flex gap-2 mt-2 flex-wrap" x-show="urls.length > 0">
                    <template x-for="(u, i) in urls" :key="i">
                        <div class="relative w-20 h-20 border rounded-lg overflow-hidden bg-gray-50 group">
                            <img :src="u" class="w-full h-full object-cover">
                            <button type="button" @click="removeImage(i)" class="absolute top-0.5 right-0.5 w-5 h-5 bg-red-500 text-white rounded-full text-xs flex items-center justify-center opacity-0 group-hover:opacity-100 transition">&times;</button>
                        </div>
                    </template>
                </div>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">태그 (쉼표 구분)</label>
                <input type="text" name="tags_text" value="{{ old('tags_text', $party->tags ? implode(', ', $party->tags) : '') }}" class="w-full px-3 py-2 border rounded-lg text-sm">
            </div>
        </fieldset>

        <div class="flex items-center gap-3 pt-4 border-t">
            <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 text-sm font-medium">
                {{ $party->exists ? '수정' : '등록' }}
            </button>
            <a href="{{ route('admin.parties.index') }}" class="px-6 py-2 bg-gray-200 text-gray-600 rounded-lg hover:bg-gray-300 text-sm">취소</a>
        </div>
    </form>
</div>
@push('scripts')
<script>
function imageUploader(initialUrl) {
    return {
        url: initialUrl || '',
        uploading: false,
        error: '',
        async upload(e) {
            const file = e.target.files[0];
            if (!file) return;
            if (file.size > 10 * 1024 * 1024) { this.error = '파일 크기는 10MB 이하만 가능합니다.'; return; }
            this.uploading = true; this.error = '';
            const fd = new FormData();
            fd.append('image', file);
            try {
                const res = await fetch('{{ route("admin.upload-image") }}', {
                    method: 'POST', body: fd,
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.url) { this.url = data.url; } else { this.error = data.message || '업로드 실패'; }
            } catch { this.error = '업로드 중 오류가 발생했습니다.'; }
            this.uploading = false;
            e.target.value = '';
        }
    };
}
function multiImageUploader(initialUrls) {
    return {
        urls: (initialUrls || []).filter(u => u && u.trim()),
        uploading: false,
        error: '',
        get urlsText() { return this.urls.join("\n"); },
        set urlsText(v) { this.urls = v.split("\n").filter(u => u.trim()); },
        removeImage(i) { this.urls.splice(i, 1); },
        async uploadMulti(e) {
            const files = Array.from(e.target.files);
            if (!files.length) return;
            this.uploading = true; this.error = '';
            for (const file of files) {
                if (file.size > 10 * 1024 * 1024) { this.error = file.name + ': 10MB 초과'; continue; }
                const fd = new FormData();
                fd.append('image', file);
                try {
                    const res = await fetch('{{ route("admin.upload-image") }}', {
                        method: 'POST', body: fd,
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }
                    });
                    const data = await res.json();
                    if (data.url) { this.urls.push(data.url); }
                } catch { this.error = '업로드 중 오류가 발생했습니다.'; }
            }
            this.uploading = false;
            e.target.value = '';
        }
    };
}
</script>
@endpush
@endsection
