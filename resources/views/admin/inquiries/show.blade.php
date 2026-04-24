@extends('admin.layouts.app')
@section('title', '문의 상세')
@section('content')
<div class="max-w-4xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.inquiries.index') }}" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg></a>
        <h1 class="text-2xl font-bold text-gray-800">문의 상세 #{{ $inquiry->id }}</h1>
    </div>

    <div class="grid md:grid-cols-3 gap-6">
        <div class="md:col-span-2 space-y-4">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-3">{{ $inquiry->subject }}</h2>
                <div class="grid grid-cols-2 gap-3 text-sm mb-4">
                    <div><span class="text-gray-400">회원:</span> {{ $inquiry->user?->name ?? '-' }} ({{ $inquiry->user?->email ?? '-' }})</div>
                    <div><span class="text-gray-400">대상:</span> {{ $inquiry->target_type==='club'?'클럽':'파티' }} #{{ $inquiry->target_id }}</div>
                    <div><span class="text-gray-400">방문일:</span> {{ $inquiry->visit_date?->format('Y-m-d') ?? '-' }}</div>
                    <div><span class="text-gray-400">인원:</span> {{ $inquiry->party_size ?? '-' }}명</div>
                    <div><span class="text-gray-400">접수일:</span> {{ $inquiry->created_at->format('Y-m-d H:i') }}</div>
                    <div><span class="text-gray-400">연락 희망:</span> {{ $inquiry->preferred_contact ?? '-' }}</div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-700 whitespace-pre-wrap">{{ $inquiry->message }}</div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700">공개 답변 스레드</h3>
                        <p class="text-xs text-gray-400 mt-1">회원에게 실제로 보이는 답변과 진행 이력을 확인합니다.</p>
                    </div>
                    <span class="rounded-full bg-purple-50 px-2.5 py-1 text-[11px] font-semibold text-purple-700">{{ $inquiry->publicReplies->count() }}건</span>
                </div>

                <div class="space-y-3">
                    @forelse($inquiry->publicReplies as $reply)
                    <div class="rounded-xl border p-4 {{ $reply->author_type !== 'user' ? 'border-purple-100 bg-purple-50/40' : 'border-gray-100 bg-gray-50/70' }}">
                        <div class="flex justify-between text-xs text-gray-400 mb-2">
                            <span class="font-semibold {{ $reply->author_type==='admin'?'text-purple-600':($reply->author_type==='md'?'text-indigo-600':'text-gray-600') }}">
                                {{ ['admin'=>'관리자','md'=>'MD','user'=>'회원'][$reply->author_type] }}
                                {{ $reply->author?->name ?? '' }}
                            </span>
                            <span>{{ $reply->created_at->format('m/d H:i') }}</span>
                        </div>
                        <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $reply->message }}</p>
                    </div>
                    @empty
                    <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50 px-4 py-5 text-sm text-gray-500">
                        아직 공개 답변이 없습니다.
                    </div>
                    @endforelse
                </div>
            </div>

            <form method="POST" action="{{ route('admin.inquiries.reply', $inquiry) }}" class="bg-white rounded-xl shadow-sm p-6 space-y-4">
                @csrf
                <div>
                    <h3 class="text-sm font-semibold text-gray-700">답변 작성</h3>
                    <p class="text-xs text-gray-400 mt-1">회원에게 전달될 답변입니다. 템플릿을 눌러 초안을 바로 채울 수 있습니다.</p>
                </div>
                @include('partials.reply-template-picker', [
                    'templates' => $replyTemplates,
                    'textareaId' => 'admin-reply-message',
                    'theme' => 'light',
                ])
                <textarea id="admin-reply-message" name="message" rows="5" required class="w-full px-3 py-2 border rounded-lg text-sm"></textarea>
                <div class="flex items-center justify-end">
                    <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700">답변 등록</button>
                </div>
            </form>
        </div>

        {{-- 사이드바 --}}
        <div class="space-y-4">
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="text-sm font-semibold text-gray-600 mb-3">상태 변경</h3>
                <form action="{{ route('admin.inquiries.status', $inquiry) }}" method="POST" class="flex gap-2">
                    @csrf @method('PATCH')
                    <select name="status" class="flex-1 px-3 py-1.5 border rounded-lg text-sm">
                        @foreach(\App\Models\Inquiry::$statuses as $v=>$l)
                            <option value="{{ $v }}" {{ $inquiry->status===$v?'selected':'' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                    <button class="px-3 py-1.5 bg-purple-600 text-white text-sm rounded-lg">변경</button>
                </form>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="text-sm font-semibold text-gray-600 mb-3">담당 MD 변경</h3>
                <form action="{{ route('admin.inquiries.assign-md', $inquiry) }}" method="POST" class="flex gap-2">
                    @csrf @method('PATCH')
                    <select name="assigned_md_id" class="flex-1 px-3 py-1.5 border rounded-lg text-sm">
                        <option value="">미배정</option>
                        @foreach($mds as $id=>$name)<option value="{{ $id }}" {{ $inquiry->assigned_md_id==$id?'selected':'' }}>{{ $name }}</option>@endforeach
                    </select>
                    <button class="px-3 py-1.5 bg-purple-600 text-white text-sm rounded-lg">변경</button>
                </form>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5">
                <div class="flex items-center justify-between gap-3 mb-3">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-600">내부 메모</h3>
                        <p class="text-xs text-gray-400 mt-1">회원에게 보이지 않는 운영 메모입니다.</p>
                    </div>
                    <span class="rounded-full bg-yellow-50 px-2.5 py-1 text-[11px] font-semibold text-yellow-700">{{ $inquiry->internalReplies->count() }}건</span>
                </div>

                <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                    @forelse($inquiry->internalReplies as $reply)
                        <div class="rounded-xl border border-yellow-100 bg-yellow-50/70 px-3 py-3">
                            <div class="flex justify-between gap-2 text-[11px] text-gray-400">
                                <span class="font-semibold text-yellow-700">
                                    {{ ['admin'=>'관리자','md'=>'MD','user'=>'회원'][$reply->author_type] ?? $reply->author_type }}
                                    {{ $reply->author?->name ?? '' }}
                                </span>
                                <span>{{ $reply->created_at->format('m/d H:i') }}</span>
                            </div>
                            <p class="mt-2 text-[12px] leading-6 text-gray-700 whitespace-pre-wrap">{{ $reply->message }}</p>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50 px-3 py-4 text-[12px] text-gray-500">
                            저장된 내부 메모가 없습니다.
                        </div>
                    @endforelse
                </div>

                <form method="POST" action="{{ route('admin.inquiries.reply', $inquiry) }}" class="mt-4 space-y-3">
                    @csrf
                    <input type="hidden" name="is_internal" value="1">
                    <textarea name="message" rows="4" required class="w-full px-3 py-2 border rounded-lg text-sm" placeholder="회원에게 보이지 않는 운영 메모를 남기세요"></textarea>
                    <button type="submit" class="w-full rounded-lg bg-yellow-500 px-4 py-2 text-sm font-medium text-white hover:bg-yellow-600">내부 메모 저장</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
