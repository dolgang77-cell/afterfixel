@extends('admin.layouts.app')
@section('title', $banner->exists ? '배너 수정' : '배너 등록')

@section('content')
<div class="max-w-2xl">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">{{ $banner->exists ? '배너 수정' : '배너 등록' }}</h1>

    <form method="POST"
          action="{{ $banner->exists ? route('admin.banners.update', $banner) : route('admin.banners.store') }}"
          class="bg-white rounded-xl shadow-sm p-6 space-y-5">
        @csrf
        @if($banner->exists) @method('PUT') @endif

        <div>
            <label class="block text-sm text-gray-600 mb-1">제목 <span class="text-red-500">*</span></label>
            <input type="text" name="title" value="{{ old('title', $banner->title) }}" required class="w-full px-3 py-2 border rounded-lg text-sm">
        </div>

        <div x-data="imageUploader('{{ old('image_url', $banner->image_url) }}')">
            <label class="block text-sm text-gray-600 mb-1">이미지 <span class="text-red-500">*</span></label>
            <div class="flex gap-3 items-start">
                <div class="flex-1 space-y-2">
                    <input type="url" name="image_url" x-model="url" required class="w-full px-3 py-2 border rounded-lg text-sm" placeholder="URL 입력 또는 이미지 업로드">
                    <label class="inline-flex items-center gap-2 px-4 py-2 bg-purple-50 text-purple-700 rounded-lg text-sm font-medium cursor-pointer hover:bg-purple-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                        <span x-text="uploading ? '업로드 중...' : '파일 선택'"></span>
                        <input type="file" accept="image/*" @change="upload($event)" class="hidden" :disabled="uploading">
                    </label>
                    <p x-show="error" x-text="error" class="text-xs text-red-500"></p>
                </div>
                <div class="shrink-0 w-32 h-20 border rounded-lg overflow-hidden bg-gray-50 flex items-center justify-center">
                    <template x-if="url">
                        <img :src="url" class="w-full h-full object-cover" x-on:error="$el.style.display='none'">
                    </template>
                    <template x-if="!url">
                        <span class="text-xs text-gray-400">미리보기</span>
                    </template>
                </div>
            </div>
        </div>

        <div>
            <label class="block text-sm text-gray-600 mb-1">링크 URL</label>
            <input type="url" name="link_url" value="{{ old('link_url', $banner->link_url) }}" class="w-full px-3 py-2 border rounded-lg text-sm">
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-600 mb-1">위치 <span class="text-red-500">*</span></label>
                <select name="position" required class="w-full px-3 py-2 border rounded-lg text-sm">
                    @foreach(['home_top' => '홈 상단', 'home_middle' => '홈 중간', 'party_top' => '파티 상단', 'club_top' => '클럽 상단'] as $val => $label)
                        <option value="{{ $val }}" {{ old('position', $banner->position) === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">정렬 순서</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $banner->sort_order ?? 0) }}" min="0" class="w-full px-3 py-2 border rounded-lg text-sm">
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-600 mb-1">시작일</label>
                <input type="date" name="start_date" value="{{ old('start_date', $banner->start_date?->format('Y-m-d')) }}" class="w-full px-3 py-2 border rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">종료일</label>
                <input type="date" name="end_date" value="{{ old('end_date', $banner->end_date?->format('Y-m-d')) }}" class="w-full px-3 py-2 border rounded-lg text-sm">
            </div>
        </div>

        <div class="flex items-center gap-3">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" id="is_active"
                   {{ old('is_active', $banner->exists ? $banner->is_active : true) ? 'checked' : '' }}
                   class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
            <label for="is_active" class="text-sm text-gray-600">활성 상태</label>
        </div>

        <div class="flex items-center gap-3 pt-4 border-t">
            <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 text-sm font-medium">
                {{ $banner->exists ? '수정' : '등록' }}
            </button>
            <a href="{{ route('admin.banners.index') }}" class="px-6 py-2 bg-gray-200 text-gray-600 rounded-lg hover:bg-gray-300 text-sm">취소</a>
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
</script>
@endpush
@endsection
