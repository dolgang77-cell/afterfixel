@extends('admin.layouts.app')
@section('title', $post->exists ? '게시글 수정' : '게시글 등록')

@section('content')
<div class="max-w-3xl">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">{{ $post->exists ? '게시글 수정' : '게시글 등록' }}</h1>

    <form method="POST"
          action="{{ $post->exists ? route('admin.posts.update', $post) : route('admin.posts.store') }}"
          class="bg-white rounded-xl shadow-sm p-6 space-y-5">
        @csrf
        @if($post->exists) @method('PUT') @endif

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-600 mb-1">작성자 닉네임</label>
                <input type="text" name="nickname" value="{{ old('nickname', $post->nickname) }}" placeholder="관리자" class="w-full px-3 py-2 border rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">유형 <span class="text-red-500">*</span></label>
                <select name="type" required class="w-full px-3 py-2 border rounded-lg text-sm">
                    @foreach(\App\Models\CommunityPost::$types as $key => $label)
                        <option value="{{ $key }}" {{ old('type', $post->type ?? 'review') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm text-gray-600 mb-1">클럽 (선택)</label>
            <select name="club_id" class="w-full px-3 py-2 border rounded-lg text-sm">
                <option value="">선택 안함</option>
                @foreach($clubs as $id => $name)
                    <option value="{{ $id }}" {{ old('club_id', $post->club_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm text-gray-600 mb-1">제목 <span class="text-red-500">*</span></label>
            <input type="text" name="title" value="{{ old('title', $post->title) }}" required class="w-full px-3 py-2 border rounded-lg text-sm">
        </div>

        <div>
            <label class="block text-sm text-gray-600 mb-1">내용 <span class="text-red-500">*</span></label>
            <textarea name="content" rows="8" required class="w-full px-3 py-2 border rounded-lg text-sm">{{ old('content', $post->content) }}</textarea>
        </div>

        <fieldset class="space-y-3">
            <legend class="text-sm font-semibold text-gray-600 uppercase tracking-wider">이미지</legend>
            <div x-data="multiImageUploader({{ json_encode(old('images_text') ? explode("\n", old('images_text')) : ($post->images ?? [])) }})">
                <textarea name="images_text" rows="3" x-model="urlsText" class="w-full px-3 py-2 border rounded-lg text-sm" placeholder="URL 입력 또는 이미지 업로드 (줄바꿈 구분)"></textarea>
                <div class="mt-2 flex items-center gap-2">
                    <label class="inline-flex items-center gap-2 px-4 py-2 bg-purple-50 text-purple-700 rounded-lg text-sm font-medium cursor-pointer hover:bg-purple-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                        <span x-text="uploading ? '업로드 중...' : '이미지 추가'"></span>
                        <input type="file" accept="image/*" multiple x-on:change="uploadMulti($event)" class="hidden" :disabled="uploading">
                    </label>
                    <p x-show="error" x-text="error" class="text-xs text-red-500"></p>
                </div>
                <div class="flex gap-2 mt-2 flex-wrap" x-show="urls.length > 0">
                    <template x-for="(u, i) in urls" :key="i">
                        <div class="relative w-20 h-20 border rounded-lg overflow-hidden bg-gray-50 group">
                            <img :src="u" class="w-full h-full object-cover">
                            <button type="button" x-on:click="removeImage(i)" class="absolute top-0.5 right-0.5 w-5 h-5 bg-red-500 text-white rounded-full text-xs flex items-center justify-center opacity-0 group-hover:opacity-100 transition">&times;</button>
                        </div>
                    </template>
                </div>
            </div>
        </fieldset>

        <div class="flex items-center gap-3 pt-4 border-t">
            <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 text-sm font-medium">
                {{ $post->exists ? '수정' : '등록' }}
            </button>
            <a href="{{ route('admin.posts.index') }}" class="px-6 py-2 bg-gray-200 text-gray-600 rounded-lg hover:bg-gray-300 text-sm">취소</a>
        </div>
    </form>
</div>
@push('scripts')
<script>
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
