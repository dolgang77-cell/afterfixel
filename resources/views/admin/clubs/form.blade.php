@extends('admin.layouts.app')
@section('title', $club->exists ? '클럽 수정' : '클럽 등록')

@section('content')
<div class="max-w-3xl">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">{{ $club->exists ? '클럽 수정' : '클럽 등록' }}</h1>

    <form method="POST"
          action="{{ $club->exists ? route('admin.clubs.update', $club) : route('admin.clubs.store') }}"
          class="bg-white rounded-xl shadow-sm p-6 space-y-6">
        @csrf
        @if($club->exists) @method('PUT') @endif

        {{-- 기본 정보 --}}
        <fieldset class="space-y-4">
            <legend class="text-sm font-semibold text-gray-600 uppercase tracking-wider">기본 정보</legend>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">클럽명 <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $club->name) }}" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">지역 <span class="text-red-500">*</span></label>
                    <select name="area" required class="w-full px-3 py-2 border rounded-lg text-sm">
                        <option value="">선택</option>
                        @foreach(\App\Models\Club::$areas as $area)
                            <option value="{{ $area }}" {{ old('area', $club->area) === $area ? 'selected' : '' }}>{{ $area }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">장르 <span class="text-red-500">*</span></label>
                    <select name="genre" required class="w-full px-3 py-2 border rounded-lg text-sm">
                        <option value="">선택</option>
                        @foreach(\App\Models\Club::$genres as $genre)
                            <option value="{{ $genre }}" {{ old('genre', $club->genre) === $genre ? 'selected' : '' }}>{{ $genre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">서브장르</label>
                    <input type="text" name="subgenre" value="{{ old('subgenre', $club->subgenre) }}" class="w-full px-3 py-2 border rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">분위기</label>
                    <input type="text" name="vibe" value="{{ old('vibe', $club->vibe) }}" class="w-full px-3 py-2 border rounded-lg text-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">설명</label>
                <textarea name="description" rows="3" class="w-full px-3 py-2 border rounded-lg text-sm">{{ old('description', $club->description) }}</textarea>
            </div>
        </fieldset>

        {{-- 운영 정보 --}}
        <fieldset class="space-y-4">
            <legend class="text-sm font-semibold text-gray-600 uppercase tracking-wider">운영 정보</legend>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">오픈 시간</label>
                    <input type="time" name="open_time" value="{{ old('open_time', $club->open_time) }}" class="w-full px-3 py-2 border rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">마감 시간</label>
                    <input type="time" name="close_time" value="{{ old('close_time', $club->close_time) }}" class="w-full px-3 py-2 border rounded-lg text-sm">
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">입장료 최소 (원)</label>
                    <input type="number" name="entry_fee_min" value="{{ old('entry_fee_min', $club->entry_fee_min) }}" min="0" step="1000" class="w-full px-3 py-2 border rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">입장료 최대 (원)</label>
                    <input type="number" name="entry_fee_max" value="{{ old('entry_fee_max', $club->entry_fee_max) }}" min="0" step="1000" class="w-full px-3 py-2 border rounded-lg text-sm">
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div class="flex items-center gap-3">
                    <input type="hidden" name="foreigner_allowed" value="0">
                    <input type="checkbox" name="foreigner_allowed" value="1" id="foreigner_allowed"
                           {{ old('foreigner_allowed', $club->foreigner_allowed) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                    <label for="foreigner_allowed" class="text-sm text-gray-600">외국인 입장 가능</label>
                </div>
                <div class="flex items-center gap-3">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" id="is_active"
                           {{ old('is_active', $club->exists ? $club->is_active : true) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                    <label for="is_active" class="text-sm text-gray-600">활성 상태</label>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">드레스코드</label>
                    <input type="text" name="dress_code" value="{{ old('dress_code', $club->dress_code) }}" placeholder="예: 스마트캐주얼" class="w-full px-3 py-2 border rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">드레스코드 상세</label>
                    <input type="text" name="dress_code_detail" value="{{ old('dress_code_detail', $club->dress_code_detail) }}" class="w-full px-3 py-2 border rounded-lg text-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">운영 가이드 / FAQ 메모</label>
                <textarea name="guide_text" rows="3" class="w-full px-3 py-2 border rounded-lg text-sm" placeholder="예: 금요일 11시 이후 혼잡, 신분증 필수, 테이블은 최소 2시간 전 문의 권장">{{ old('guide_text', $club->guide_text) }}</textarea>
                <p class="mt-1 text-xs text-gray-500">상세 페이지의 운영 FAQ 바텀시트와 이용 가이드 영역에 함께 노출됩니다.</p>
            </div>
        </fieldset>

        {{-- 위치/연락처 --}}
        <fieldset class="space-y-4">
            <legend class="text-sm font-semibold text-gray-600 uppercase tracking-wider">위치 / 연락처</legend>

            <div>
                <label class="block text-sm text-gray-600 mb-1">주소</label>
                <input type="text" name="address" value="{{ old('address', $club->address) }}" class="w-full px-3 py-2 border rounded-lg text-sm">
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">위도</label>
                    <input type="number" name="lat" value="{{ old('lat', $club->lat) }}" step="0.000001" class="w-full px-3 py-2 border rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">경도</label>
                    <input type="number" name="lng" value="{{ old('lng', $club->lng) }}" step="0.000001" class="w-full px-3 py-2 border rounded-lg text-sm">
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">전화번호</label>
                    <input type="text" name="phone" value="{{ old('phone', $club->phone) }}" class="w-full px-3 py-2 border rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">인스타그램</label>
                    <input type="text" name="instagram" value="{{ old('instagram', $club->instagram) }}" placeholder="@username" class="w-full px-3 py-2 border rounded-lg text-sm">
                </div>
            </div>
        </fieldset>

        {{-- 미디어 --}}
        <fieldset class="space-y-4">
            <legend class="text-sm font-semibold text-gray-600 uppercase tracking-wider">미디어</legend>

            <div x-data="imageUploader('{{ old('thumbnail', $club->thumbnail) }}')">
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
            <div x-data="multiImageUploader({{ json_encode(old('images_text') ? explode("\n", old('images_text')) : ($club->images ?? [])) }})">
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
                <input type="text" name="tags_text" value="{{ old('tags_text', $club->tags ? implode(', ', $club->tags) : '') }}" placeholder="핫플, 루프탑, VIP" class="w-full px-3 py-2 border rounded-lg text-sm">
            </div>
        </fieldset>

        {{-- 평점 (수동 입력) --}}
        <fieldset class="space-y-4">
            <legend class="text-sm font-semibold text-gray-600 uppercase tracking-wider">평점</legend>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">평균 평점 (0~5)</label>
                    <input type="number" name="rating_avg" value="{{ old('rating_avg', $club->rating_avg) }}" min="0" max="5" step="0.1" class="w-full px-3 py-2 border rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">리뷰 수</label>
                    <input type="number" name="rating_count" value="{{ old('rating_count', $club->rating_count) }}" min="0" class="w-full px-3 py-2 border rounded-lg text-sm">
                </div>
            </div>
        </fieldset>

        {{-- 버튼 --}}
        <div class="flex items-center gap-3 pt-4 border-t">
            <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 text-sm font-medium">
                {{ $club->exists ? '수정' : '등록' }}
            </button>
            <a href="{{ route('admin.clubs.index') }}" class="px-6 py-2 bg-gray-200 text-gray-600 rounded-lg hover:bg-gray-300 text-sm">취소</a>
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
