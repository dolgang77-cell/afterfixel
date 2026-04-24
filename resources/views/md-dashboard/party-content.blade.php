@extends('md-dashboard.layout')
@section('title', $party->name . ' 소개 편집')
@section('content')
<div class="space-y-4">
    <div class="flex items-center gap-3">
        <a href="{{ route('md-dashboard.parties') }}" class="flex h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-white/5 text-slate-300">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        </a>
        <div>
            <h1 class="text-[20px] font-black text-white">{{ $party->name }}</h1>
            <p class="text-[12px] text-slate-400">{{ $party->club?->name }} · 파티 소개와 이미지를 모바일에서 즉시 수정합니다.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('md-dashboard.parties.content.update', $party) }}" class="space-y-4 rounded-[28px] border border-white/10 bg-white/5 p-5">
        @csrf @method('PATCH')
        <div><label class="mb-2 block text-[12px] font-semibold text-slate-300">소개 제목</label><input type="text" name="intro_title" value="{{ old('intro_title', $party->intro_title) }}" class="w-full rounded-2xl border border-white/10 bg-slate-900/70 px-4 py-3 text-[14px] text-white"></div>
        <div><label class="mb-2 block text-[12px] font-semibold text-slate-300">한줄 소개</label><input type="text" name="short_description" value="{{ old('short_description', $party->short_description) }}" class="w-full rounded-2xl border border-white/10 bg-slate-900/70 px-4 py-3 text-[14px] text-white" maxlength="200"></div>
        <div class="space-y-2" x-data="mdRichEditor({ ownerType: 'party', ownerId: {{ $party->id }} })" x-init="init()">
            <label class="block text-[12px] font-semibold text-slate-300">상세 소개</label>
            <div class="flex gap-2 overflow-x-auto scrollbar-hide md-scroll-snap pb-1">
                <button type="button" @click="block('p')" class="shrink-0 rounded-full bg-white/5 px-3 py-2 text-[11px] font-semibold text-slate-200">본문</button>
                <button type="button" @click="block('h3')" class="shrink-0 rounded-full bg-white/5 px-3 py-2 text-[11px] font-semibold text-slate-200">제목</button>
                <button type="button" @click="exec('bold')" class="shrink-0 rounded-full bg-white/5 px-3 py-2 text-[11px] font-semibold text-slate-200">굵게</button>
                <button type="button" @click="exec('insertUnorderedList')" class="shrink-0 rounded-full bg-white/5 px-3 py-2 text-[11px] font-semibold text-slate-200">목록</button>
                <button type="button" @click="exec('formatBlock', '<blockquote>')" class="shrink-0 rounded-full bg-white/5 px-3 py-2 text-[11px] font-semibold text-slate-200">인용</button>
                <button type="button" @click="insertLink()" class="shrink-0 rounded-full bg-white/5 px-3 py-2 text-[11px] font-semibold text-slate-200">링크</button>
                <label class="shrink-0 cursor-pointer rounded-full bg-indigo-500/20 px-3 py-2 text-[11px] font-semibold text-indigo-100">
                    <span x-text="uploadingInline ? '업로드 중...' : '본문 이미지'"></span>
                    <input type="file" accept="image/*" class="hidden" :disabled="uploadingInline" @change="uploadInlineImage($event)">
                </label>
            </div>
            <div x-ref="editor" contenteditable="true" data-placeholder="파티 라인업, 분위기, 입장 포인트를 이미지와 함께 정리하세요." @input="sync()" class="md-editor-surface rounded-[24px] border border-white/10 bg-slate-900/70 px-4 py-4 text-[14px] text-white focus:outline-none"></div>
            <input x-ref="input" type="hidden" name="full_description" value="{{ \App\Services\RichContentService::editorValue(old('full_description', $party->full_description)) }}">
            <p x-show="error" x-text="error" class="text-[11px] text-rose-300"></p>
        </div>
        <div class="space-y-2" x-data="mdRichEditor({ ownerType: 'party', ownerId: {{ $party->id }} })" x-init="init()">
            <label class="block text-[12px] font-semibold text-slate-300">이용 가이드</label>
            <div class="flex gap-2 overflow-x-auto scrollbar-hide md-scroll-snap pb-1">
                <button type="button" @click="block('p')" class="shrink-0 rounded-full bg-white/5 px-3 py-2 text-[11px] font-semibold text-slate-200">본문</button>
                <button type="button" @click="block('h4')" class="shrink-0 rounded-full bg-white/5 px-3 py-2 text-[11px] font-semibold text-slate-200">소제목</button>
                <button type="button" @click="exec('bold')" class="shrink-0 rounded-full bg-white/5 px-3 py-2 text-[11px] font-semibold text-slate-200">굵게</button>
                <button type="button" @click="exec('insertUnorderedList')" class="shrink-0 rounded-full bg-white/5 px-3 py-2 text-[11px] font-semibold text-slate-200">목록</button>
                <button type="button" @click="insertLink()" class="shrink-0 rounded-full bg-white/5 px-3 py-2 text-[11px] font-semibold text-slate-200">링크</button>
                <label class="shrink-0 cursor-pointer rounded-full bg-indigo-500/20 px-3 py-2 text-[11px] font-semibold text-indigo-100">
                    <span x-text="uploadingInline ? '업로드 중...' : '가이드 이미지'"></span>
                    <input type="file" accept="image/*" class="hidden" :disabled="uploadingInline" @change="uploadInlineImage($event)">
                </label>
            </div>
            <div x-ref="editor" contenteditable="true" data-placeholder="예약 팁, 입장 순서, 확인사항을 실무형 가이드로 작성하세요." @input="sync()" class="md-editor-surface rounded-[24px] border border-white/10 bg-slate-900/70 px-4 py-4 text-[14px] text-white focus:outline-none"></div>
            <input x-ref="input" type="hidden" name="guide_text" value="{{ \App\Services\RichContentService::editorValue(old('guide_text', $party->guide_text)) }}">
            <p x-show="error" x-text="error" class="text-[11px] text-rose-300"></p>
        </div>
        <button type="submit" class="w-full rounded-2xl bg-white px-4 py-3 text-[14px] font-black text-slate-950">파티 소개 저장</button>
    </form>

    <section class="rounded-[28px] border border-white/10 bg-white/5 p-5" x-data="mdUploader('party', {{ $party->id }})">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-[16px] font-black text-white">파티 이미지 관리</h2>
                <p class="mt-1 text-[12px] leading-relaxed text-slate-400">담당 파티에 올린 이미지는 자동 승인되어 바로 노출됩니다. 정렬과 삭제도 같은 화면에서 처리합니다.</p>
            </div>
            <label class="shrink-0 cursor-pointer rounded-full bg-indigo-500 px-4 py-2 text-[12px] font-semibold text-white">
                <span x-text="uploading ? '업로드 중...' : '이미지 추가'"></span>
                <input type="file" accept="image/*" multiple class="hidden" :disabled="uploading" @change="uploadFiles($event)">
            </label>
        </div>
        <p x-show="error" x-text="error" class="mt-2 text-[11px] text-rose-300"></p>
        <p x-show="success" x-text="success" class="mt-2 text-[11px] text-emerald-300"></p>

        <div x-ref="mediaGrid" class="mt-4 grid grid-cols-2 gap-3">
            @forelse($media as $m)
                <div class="overflow-hidden rounded-[24px] border border-white/10 bg-slate-900/70">
                    <img src="{{ $m->file_url }}" class="h-36 w-full object-cover">
                    <div class="space-y-2 p-3">
                        <div class="flex items-center justify-between gap-2">
                            <span class="rounded-full {{ $m->approval_status === 'approved' ? 'bg-emerald-500/15 text-emerald-200' : 'bg-amber-500/15 text-amber-200' }} px-2 py-1 text-[10px] font-semibold">{{ $m->approval_status === 'approved' ? '즉시 노출' : $m->approval_status }}</span>
                            <span class="text-[10px] text-slate-500">순서 {{ $m->sort_order + 1 }}</span>
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
            @empty
                <div data-empty-state class="col-span-2 rounded-[24px] border border-dashed border-white/10 bg-slate-900/40 p-6 text-center text-[12px] text-slate-400">등록된 이미지가 없습니다.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
