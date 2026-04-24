@extends('admin.layouts.app')
@section('title', $md->exists ? 'MD 수정' : 'MD 등록')

@section('content')
<div class="max-w-3xl">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">{{ $md->exists ? 'MD 수정' : 'MD 등록' }}</h1>

    <form method="POST"
          action="{{ $md->exists ? route('admin.md.update', $md) : route('admin.md.store') }}"
          class="bg-white rounded-xl shadow-sm p-6 space-y-5">
        @csrf
        @if($md->exists) @method('PUT') @endif

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-600 mb-1">표시 이름 <span class="text-red-500">*</span></label>
                <input type="text" name="display_name" value="{{ old('display_name', $md->display_name) }}" required class="w-full px-3 py-2 border rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">연결 계정 (MD 회원)</label>
                <select name="user_id" class="w-full px-3 py-2 border rounded-lg text-sm">
                    <option value="">미연결</option>
                    @foreach($users as $id => $name)
                        <option value="{{ $id }}" {{ old('user_id', $md->user_id) == $id ? 'selected' : '' }}>{{ $name }} (ID:{{ $id }})</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div x-data="imageUploader('{{ old('profile_image', $md->profile_image) }}')">
            <label class="block text-sm text-gray-600 mb-1">프로필 이미지</label>
            <div class="flex gap-3 items-start">
                <div class="flex-1 space-y-2">
                    <input type="text" name="profile_image" x-model="url" class="w-full px-3 py-2 border rounded-lg text-sm" placeholder="URL 입력 또는 이미지 업로드">
                    <label class="inline-flex items-center gap-2 px-4 py-2 bg-purple-50 text-purple-700 rounded-lg text-sm font-medium cursor-pointer hover:bg-purple-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                        <span x-text="uploading ? '업로드 중...' : '파일 선택'"></span>
                        <input type="file" accept="image/*" x-on:change="upload($event)" class="hidden" :disabled="uploading">
                    </label>
                </div>
                <div class="shrink-0 w-20 h-20 border rounded-full overflow-hidden bg-gray-50 flex items-center justify-center">
                    <template x-if="url"><img :src="url" class="w-full h-full object-cover"></template>
                    <template x-if="!url"><span class="text-xs text-gray-400">미리보기</span></template>
                </div>
            </div>
        </div>

        <div>
            <label class="block text-sm text-gray-600 mb-1">소개 문구</label>
            <textarea name="intro" rows="3" class="w-full px-3 py-2 border rounded-lg text-sm">{{ old('intro', $md->intro) }}</textarea>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-600 mb-1">담당 지역 (쉼표 구분)</label>
                <input type="text" name="areas_text" value="{{ old('areas_text', $md->areas ? implode(', ', $md->areas) : '') }}" placeholder="강남, 홍대, 부산 서면, 제주시" class="w-full px-3 py-2 border rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">담당 장르 (쉼표 구분)</label>
                <input type="text" name="genres_text" value="{{ old('genres_text', $md->genres ? implode(', ', $md->genres) : '') }}" placeholder="EDM, Hip-Hop" class="w-full px-3 py-2 border rounded-lg text-sm">
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-600 mb-1">소속</label>
                <input type="text" name="affiliation" value="{{ old('affiliation', $md->affiliation) }}" class="w-full px-3 py-2 border rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">외부 링크 (공개용)</label>
                <input type="text" name="external_link" value="{{ old('external_link', $md->external_link) }}" placeholder="인스타그램 등" class="w-full px-3 py-2 border rounded-lg text-sm">
            </div>
        </div>

        <div>
            <label class="block text-sm text-gray-600 mb-1">연락처 (관리자 전용, 비공개)</label>
            <input type="text" name="contact_info" value="{{ old('contact_info', $md->contact_info) }}" class="w-full px-3 py-2 border rounded-lg text-sm">
        </div>

        <div>
            <label class="block text-sm text-gray-600 mb-1">관리자 메모 (비공개)</label>
            <textarea name="admin_memo" rows="2" class="w-full px-3 py-2 border rounded-lg text-sm">{{ old('admin_memo', $md->admin_memo) }}</textarea>
        </div>

        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm text-gray-600 mb-1">상태 <span class="text-red-500">*</span></label>
                <select name="status" required class="w-full px-3 py-2 border rounded-lg text-sm">
                    @foreach(['active' => '활동중', 'inactive' => '비활동', 'hidden' => '숨김'] as $val => $label)
                        <option value="{{ $val }}" {{ old('status', $md->status ?? 'active') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">우선순위</label>
                <input type="number" name="priority" value="{{ old('priority', $md->priority ?? 0) }}" min="0" class="w-full px-3 py-2 border rounded-lg text-sm">
            </div>
            <div class="flex items-end pb-1">
                <label class="flex items-center gap-2">
                    <input type="hidden" name="visible" value="0">
                    <input type="checkbox" name="visible" value="1" {{ old('visible', $md->exists ? $md->visible : true) ? 'checked' : '' }} class="rounded border-gray-300 text-purple-600">
                    <span class="text-sm text-gray-600">사용자에게 노출</span>
                </label>
            </div>
        </div>

        <div class="flex items-center gap-3 pt-4 border-t">
            <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 text-sm font-medium">{{ $md->exists ? '수정' : '등록' }}</button>
            <a href="{{ route('admin.md.index') }}" class="px-6 py-2 bg-gray-200 text-gray-600 rounded-lg hover:bg-gray-300 text-sm">취소</a>
        </div>
    </form>
</div>
@push('scripts')
<script>
function imageUploader(initialUrl) {
    return {
        url: initialUrl || '', uploading: false, error: '',
        async upload(e) {
            const file = e.target.files[0]; if (!file) return;
            if (file.size > 10*1024*1024) { this.error = '10MB 초과'; return; }
            this.uploading = true; this.error = '';
            const fd = new FormData(); fd.append('image', file);
            try {
                const res = await fetch('{{ route("admin.upload-image") }}', { method:'POST', body:fd, headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Accept':'application/json'} });
                const data = await res.json(); if(data.url) this.url = data.url; else this.error = data.message||'실패';
            } catch { this.error = '오류 발생'; }
            this.uploading = false; e.target.value = '';
        }
    };
}
</script>
@endpush
@endsection
