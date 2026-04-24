@extends('md-dashboard.layout')
@section('title', '프로필 수정')
@section('content')
<div class="space-y-4">
    <section class="rounded-[28px] border border-white/10 bg-white/5 p-5" x-data="mdUploader('md_profile', {{ $md->id }})">
        <div class="flex items-start gap-4">
            @if($md->profile_image)
                @php $profileSrcset = $md->profile_image_srcset; @endphp
                <img src="{{ $md->profile_image }}" class="h-20 w-20 rounded-[24px] object-cover" decoding="async"
                     @if($profileSrcset) srcset="{{ $profileSrcset }}" sizes="80px" @endif>
            @else
                <div class="flex h-20 w-20 items-center justify-center rounded-[24px] bg-indigo-500/20 text-[28px] font-black text-white">{{ mb_substr($md->display_name, 0, 1) }}</div>
            @endif
            <div class="flex-1">
                <h1 class="text-[20px] font-black text-white">내 프로필</h1>
                <p class="mt-1 text-[12px] leading-relaxed text-slate-400">프로필 사진은 본인 계정 기준으로만 수정됩니다. MD 프로필 이미지는 업로드 즉시 노출됩니다.</p>
                <label class="mt-3 inline-flex cursor-pointer items-center gap-2 rounded-full bg-indigo-500 px-4 py-2 text-[12px] font-semibold text-white">
                    <span x-text="uploading ? '업로드 중...' : '프로필 사진 변경'"></span>
                    <input type="file" accept="image/*" class="hidden" :disabled="uploading" @change="uploadFiles($event)">
                </label>
                <p x-show="error" x-text="error" class="mt-2 text-[11px] text-rose-300"></p>
            </div>
        </div>
    </section>

    <form method="POST" action="{{ route('md-dashboard.profile.update') }}" class="space-y-4 rounded-[28px] border border-white/10 bg-white/5 p-5">
        @csrf @method('PATCH')
        <div>
            <label class="mb-2 block text-[12px] font-semibold text-slate-300">표시 이름</label>
            <input type="text" name="display_name" value="{{ old('display_name', $md->display_name) }}" required class="w-full rounded-2xl border border-white/10 bg-slate-900/70 px-4 py-3 text-[14px] text-white">
        </div>
        <div>
            <label class="mb-2 block text-[12px] font-semibold text-slate-300">소개 문구</label>
            <textarea name="intro" rows="5" class="w-full rounded-2xl border border-white/10 bg-slate-900/70 px-4 py-3 text-[14px] text-white">{{ old('intro', $md->intro) }}</textarea>
        </div>
        <div>
            <label class="mb-2 block text-[12px] font-semibold text-slate-300">공개 연락 링크</label>
            <input type="text" name="external_link" value="{{ old('external_link', $md->external_link) }}" class="w-full rounded-2xl border border-white/10 bg-slate-900/70 px-4 py-3 text-[14px] text-white">
        </div>
        <div>
            <label class="mb-2 block text-[12px] font-semibold text-slate-300">연락 메모</label>
            <input type="text" name="contact_info" value="{{ old('contact_info', $md->contact_info) }}" class="w-full rounded-2xl border border-white/10 bg-slate-900/70 px-4 py-3 text-[14px] text-white">
        </div>
        <div class="rounded-2xl border border-white/10 bg-slate-900/60 px-4 py-3 text-[12px] text-slate-400">
            담당 지역: {{ $md->areas ? implode(', ', $md->areas) : '-' }}<br>
            담당 장르: {{ $md->genres ? implode(', ', $md->genres) : '-' }}
        </div>
        <button type="submit" class="w-full rounded-2xl bg-white px-4 py-3 text-[14px] font-black text-slate-950">프로필 저장</button>
    </form>

    @if($profileMedia->isNotEmpty())
    <section class="rounded-[28px] border border-white/10 bg-white/5 p-5">
        <div class="mb-3 flex items-center justify-between">
            <h2 class="text-[15px] font-black text-white">프로필 이미지 기록</h2>
            <p class="text-[11px] text-slate-400">정렬과 삭제 가능</p>
        </div>
        <div class="grid grid-cols-2 gap-3">
            @foreach($profileMedia as $m)
                <div class="overflow-hidden rounded-[24px] border border-white/10 bg-slate-900/70">
                    <img src="{{ $m->file_url }}" class="h-32 w-full object-cover">
                    <div class="space-y-2 p-3">
                        <div class="flex items-center justify-between">
                            <span class="rounded-full bg-emerald-500/15 px-2 py-1 text-[10px] font-semibold text-emerald-200">{{ $m->approval_status === 'approved' ? '즉시 노출' : $m->approval_status }}</span>
                            <span class="text-[10px] text-slate-500">#{{ $m->sort_order }}</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <form method="POST" action="{{ route('md-dashboard.media.order', $m) }}">@csrf @method('PATCH')
                                <input type="hidden" name="direction" value="up">
                                <button class="w-full rounded-xl bg-white/5 px-2 py-2 text-[11px] font-semibold text-slate-200">위로</button>
                            </form>
                            <form method="POST" action="{{ route('md-dashboard.media.order', $m) }}">@csrf @method('PATCH')
                                <input type="hidden" name="direction" value="down">
                                <button class="w-full rounded-xl bg-white/5 px-2 py-2 text-[11px] font-semibold text-slate-200">아래</button>
                            </form>
                            <form method="POST" action="{{ route('md-dashboard.media.destroy', $m) }}">@csrf @method('DELETE')
                                <button class="w-full rounded-xl bg-rose-500/15 px-2 py-2 text-[11px] font-semibold text-rose-200">삭제</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
    @endif
</div>
@endsection
