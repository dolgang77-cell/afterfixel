@extends('layouts.app')
@section('title', __('community.write_title'))

@section('content')
<div class="px-4 py-5 space-y-5">
    <div class="flex items-center gap-3">
        <a href="{{ route('community.index') }}" class="w-9 h-9 flex items-center justify-center rounded-full bg-dark-700/50 text-gray-400 active:scale-90 transition-transform border border-white/[0.04]">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        </a>
        <h1 class="text-xl font-extrabold tracking-tight">{{ __('community.write_title') }}</h1>
    </div>

    @guest
    <div class="card p-5 text-center space-y-3">
        <p class="text-[13px] text-gray-400">{{ __('community.login_to_write') }}</p>
        <a href="{{ route('login') }}" class="btn-primary inline-block px-6 py-3 rounded-2xl text-[13px] font-bold text-white">{{ __('common.login') }}</a>
    </div>
    @else
    <form action="{{ route('community.store') }}" method="POST" class="space-y-4">
        @csrf

        {{-- 작성자 표시 --}}
        <div class="card p-4 flex items-center gap-3">
            <div class="w-9 h-9 rounded-full gradient-accent flex items-center justify-center text-white text-[13px] font-bold">{{ mb_substr(auth()->user()->nickname, 0, 1) }}</div>
            <div>
                <p class="text-[13px] font-semibold text-gray-200">{{ auth()->user()->nickname }}</p>
                <p class="text-[10px] text-gray-600">{{ __('community.author') }}</p>
            </div>
        </div>

        {{-- Type --}}
        <div class="card p-4">
            <label class="text-[13px] font-bold text-gray-300 mb-2.5 block">{{ __('community.type') }}</label>
            <div class="grid grid-cols-3 gap-2">
                @foreach(['review' => '💬 ' . __('community.type_review'), 'tip' => '💡 ' . __('community.type_tip'), 'realtime' => '🔴 ' . __('community.type_realtime')] as $val => $label)
                <label class="cursor-pointer">
                    <input type="radio" name="type" value="{{ $val }}" class="hidden peer" {{ old('type', 'review') === $val ? 'checked' : '' }}>
                    <div class="text-center py-2.5 rounded-xl text-[11px] font-semibold border border-white/[0.04] bg-dark-700/40 text-gray-500
                                peer-checked:gradient-accent peer-checked:text-white peer-checked:border-transparent peer-checked:shadow-glow-sm
                                transition-all active:scale-95">
                        {{ $label }}
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        {{-- Club --}}
        <div class="card p-4">
            <label class="text-[13px] font-bold text-gray-300 mb-2.5 block">{{ __('community.related_club') }} <span class="text-[10px] text-gray-600 font-normal">({{ __('community.optional') }})</span></label>
            <select name="club_id"
                    class="w-full bg-dark-700/50 border border-white/[0.06] rounded-xl px-4 py-3 text-[13px] text-white transition-all appearance-none"
                    style="background-image: url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%236b7280%22 stroke-width=%222%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 d=%22M19 9l-7 7-7-7%22/%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 12px center; background-size: 16px;">
                <option value="">{{ __('community.select_none') }}</option>
                @foreach($clubs as $club)
                <option value="{{ $club->id }}" {{ old('club_id') == $club->id ? 'selected' : '' }}>{{ $club->name }} ({{ trans_auto($club->area) }})</option>
                @endforeach
            </select>
        </div>

        {{-- Title --}}
        <div class="card p-4">
            <label class="text-[13px] font-bold text-gray-300 mb-2.5 block">{{ __('community.title_label') }}</label>
            <input type="text" name="title" value="{{ old('title') }}" required maxlength="100" placeholder="{{ __('community.title_placeholder') }}"
                   class="w-full bg-dark-700/50 border border-white/[0.06] rounded-xl px-4 py-3 text-[13px] text-white placeholder-gray-600 transition-all">
        </div>

        {{-- Content --}}
        <div class="card p-4">
            <label class="text-[13px] font-bold text-gray-300 mb-2.5 block">{{ __('community.body_label') }}</label>
            <textarea name="content" required maxlength="2000" rows="6" placeholder="{{ __('community.body_placeholder') }}"
                      class="w-full bg-dark-700/50 border border-white/[0.06] rounded-xl px-4 py-3 text-[13px] text-white placeholder-gray-600 resize-none transition-all leading-relaxed">{{ old('content') }}</textarea>
            <p class="text-[10px] text-gray-600 mt-1.5 text-right">{{ __('community.max_chars') }}</p>
        </div>

        {{-- 이미지 첨부 --}}
        <div class="card p-4" x-data="communityImageUploader()">
            <label class="text-[13px] font-bold text-gray-300 mb-2.5 block">{{ __('community.image_label') }} <span class="text-[10px] text-gray-600 font-normal">({{ __('community.image_max') }})</span></label>
            <label class="inline-flex items-center gap-2 px-4 py-2 bg-accent/10 text-accent rounded-xl text-[12px] font-semibold cursor-pointer active:scale-95 transition-transform">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                <span x-text="uploading ? '업로드 중...' : '사진 추가'"></span>
                <input type="file" accept="image/*" multiple x-on:change="upload($event)" class="hidden" :disabled="uploading">
            </label>
            <input type="hidden" name="uploaded_image_ids" x-model="imageIds">
            <div class="flex gap-2 mt-3 flex-wrap" x-show="images.length > 0">
                <template x-for="(img, i) in images" :key="i">
                    <div class="relative w-20 h-20 rounded-xl overflow-hidden border border-white/[0.06] group">
                        <img :src="img.url" class="w-full h-full object-cover">
                        <button type="button" x-on:click="removeImage(i)" class="absolute top-0.5 right-0.5 w-5 h-5 bg-red-500 text-white rounded-full text-xs flex items-center justify-center opacity-0 group-hover:opacity-100 transition">&times;</button>
                        <div x-show="img.status==='pending'" class="absolute bottom-0 left-0 right-0 bg-yellow-500/80 text-[8px] text-center text-white py-0.5">{{ __('community.pending_badge') }}</div>
                    </div>
                </template>
            </div>
            <p x-show="error" x-text="error" class="text-[11px] text-red-400 mt-1"></p>
        </div>

        {{-- Submit --}}
        <button type="submit" class="btn-primary w-full py-4 rounded-2xl font-bold text-[14px] text-white">
            {{ __('community.post_btn') }}
        </button>
    </form>
    @endguest
</div>

@push('scripts')
<script>
function communityImageUploader() {
    return {
        images: [], uploading: false, error: '',
        get imageIds() { return this.images.map(i => i.id).join(','); },
        async upload(e) {
            const files = Array.from(e.target.files);
            if (this.images.length + files.length > 5) { this.error = '최대 5장까지 첨부 가능합니다.'; return; }
            this.uploading = true; this.error = '';
            for (const file of files) {
                if (file.size > 10*1024*1024) { this.error = file.name + ': 10MB 초과'; continue; }
                const fd = new FormData();
                fd.append('image', file); fd.append('owner_type', 'community'); fd.append('owner_id', 0);
                try {
                    const res = await fetch('{{ route("media.upload") }}', { method:'POST', body:fd, headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Accept':'application/json'} });
                    const data = await res.json();
                    if (data.url) this.images.push({ id: data.id, url: data.url, status: data.approval_status });
                    else this.error = data.message || '업로드 실패';
                } catch { this.error = '업로드 중 오류가 발생했습니다.'; }
            }
            this.uploading = false; e.target.value = '';
        },
        removeImage(i) { this.images.splice(i, 1); }
    };
}
</script>
@endpush
@endsection
