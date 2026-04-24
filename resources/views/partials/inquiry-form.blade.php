@php
    $hasDetailValues = old('preferred_contact') || old('budget_min') || old('budget_max') || old('visit_time_slot') || old('gender_mix') || old('special_request');
    $trackTargetType = $trackTargetType ?? null;
    $trackTargetId = $trackTargetId ?? null;
@endphp

@auth
<form action="{{ $action }}" method="POST" class="card p-4 space-y-4" x-data="{ openDetails: {{ $hasDetailValues ? 'true' : 'false' }} }"
      data-track-event="inquiry_submit"
      data-track-trigger="submit"
      data-track-target-type="{{ $trackTargetType }}"
      data-track-target-id="{{ $trackTargetId }}"
      data-track-context="{{ $formId }}">
    @csrf
    <input type="hidden" name="intent_type" value="question">

    <div class="space-y-1.5">
        <p class="text-[14px] font-bold text-white">상담 시작하기</p>
        <p class="text-[12px] text-gray-400">{{ $helperText }}</p>
    </div>

    <div class="rounded-2xl border border-white/[0.06] bg-dark-700/40 p-3">
        <label for="{{ $formId }}-message" class="block text-[12px] font-semibold text-gray-200 mb-2">문의 내용</label>
        <textarea
            id="{{ $formId }}-message"
            name="message"
            rows="4"
            required
            class="w-full bg-dark-700 border border-white/[0.06] rounded-xl px-3 py-3 text-[13px] text-white placeholder-gray-600 focus:border-accent focus:outline-none"
            placeholder="{{ $messagePlaceholder }}"
        >{{ old('message') }}</textarea>
        <p class="mt-2 text-[11px] text-gray-500">핵심 질문만 적어도 접수됩니다. 일정과 예산은 아래에서 선택 입력할 수 있습니다.</p>
    </div>

    <div class="grid grid-cols-2 gap-2">
        <div class="rounded-2xl border border-white/[0.06] bg-dark-700/30 px-3 py-3">
            <label for="{{ $formId }}-visit-date" class="block text-[11px] font-semibold text-gray-300 mb-1.5">방문 예정일</label>
            <input
                id="{{ $formId }}-visit-date"
                type="date"
                name="visit_date"
                value="{{ old('visit_date') }}"
                class="w-full bg-dark-700 border border-white/[0.06] rounded-xl px-3 py-2 text-[12px] text-gray-300"
            >
        </div>
        <div class="rounded-2xl border border-white/[0.06] bg-dark-700/30 px-3 py-3">
            <label for="{{ $formId }}-party-size" class="block text-[11px] font-semibold text-gray-300 mb-1.5">인원</label>
            <input
                id="{{ $formId }}-party-size"
                type="number"
                name="party_size"
                min="1"
                max="100"
                value="{{ old('party_size') }}"
                class="w-full bg-dark-700 border border-white/[0.06] rounded-xl px-3 py-2 text-[12px] text-gray-300"
                placeholder="예: 4"
            >
        </div>
    </div>

    <button
        type="button"
        @click="openDetails = !openDetails"
        class="flex w-full items-center justify-between rounded-2xl border border-white/[0.06] bg-dark-700/30 px-3 py-3 text-left"
    >
        <div>
            <p class="text-[12px] font-semibold text-gray-200">상세 정보 추가</p>
            <p class="mt-0.5 text-[11px] text-gray-500">예산, 도착 시간, 연락 수단, 추가 요청</p>
        </div>
        <span class="text-[11px] font-semibold text-accent" x-text="openDetails ? '접기' : '열기'"></span>
    </button>

    <div x-show="openDetails" x-cloak class="space-y-3">
        <div class="grid grid-cols-2 gap-2">
            <div class="rounded-2xl border border-white/[0.06] bg-dark-700/30 px-3 py-3">
                <label for="{{ $formId }}-visit-time-slot" class="block text-[11px] font-semibold text-gray-300 mb-1.5">도착 시간대</label>
                <select
                    id="{{ $formId }}-visit-time-slot"
                    name="visit_time_slot"
                    class="w-full bg-dark-700 border border-white/[0.06] rounded-xl px-3 py-2 text-[12px] text-gray-300"
                >
                    <option value="">선택 안 함</option>
                    @foreach(\App\Models\Inquiry::$visitTimeSlots as $slotValue => $slotLabel)
                        <option value="{{ $slotValue }}" {{ old('visit_time_slot') === $slotValue ? 'selected' : '' }}>{{ $slotLabel }}</option>
                    @endforeach
                </select>
            </div>
            <div class="rounded-2xl border border-white/[0.06] bg-dark-700/30 px-3 py-3">
                <label for="{{ $formId }}-gender-mix" class="block text-[11px] font-semibold text-gray-300 mb-1.5">인원 구성</label>
                <input
                    id="{{ $formId }}-gender-mix"
                    type="text"
                    name="gender_mix"
                    maxlength="50"
                    value="{{ old('gender_mix') }}"
                    class="w-full bg-dark-700 border border-white/[0.06] rounded-xl px-3 py-2 text-[12px] text-gray-300"
                    placeholder="예: 남2 여2"
                >
            </div>
        </div>

        <div class="rounded-2xl border border-white/[0.06] bg-dark-700/30 px-3 py-3">
            <p class="text-[11px] font-semibold text-gray-300">예산 범위</p>
            <div class="mt-2 grid grid-cols-2 gap-2">
                <input
                    type="number"
                    name="budget_min"
                    min="0"
                    step="10000"
                    value="{{ old('budget_min') }}"
                    class="w-full bg-dark-700 border border-white/[0.06] rounded-xl px-3 py-2 text-[12px] text-gray-300"
                    placeholder="최소 예산"
                >
                <input
                    type="number"
                    name="budget_max"
                    min="0"
                    step="10000"
                    value="{{ old('budget_max') }}"
                    class="w-full bg-dark-700 border border-white/[0.06] rounded-xl px-3 py-2 text-[12px] text-gray-300"
                    placeholder="최대 예산"
                >
            </div>
            <p class="mt-2 text-[11px] text-gray-500">{{ $budgetGuideText }}</p>
        </div>

        <div class="rounded-2xl border border-white/[0.06] bg-dark-700/30 px-3 py-3">
            <label for="{{ $formId }}-preferred-contact" class="block text-[11px] font-semibold text-gray-300 mb-1.5">연락 선호</label>
            <input
                id="{{ $formId }}-preferred-contact"
                type="text"
                name="preferred_contact"
                value="{{ old('preferred_contact') }}"
                class="w-full bg-dark-700 border border-white/[0.06] rounded-xl px-3 py-2 text-[12px] text-gray-300"
                placeholder="예: 카카오톡 아이디 / 문자 / 전화"
            >
        </div>

        <div class="rounded-2xl border border-white/[0.06] bg-dark-700/30 px-3 py-3">
            <label for="{{ $formId }}-special-request" class="block text-[11px] font-semibold text-gray-300 mb-1.5">추가 요청</label>
            <textarea
                id="{{ $formId }}-special-request"
                name="special_request"
                rows="3"
                class="w-full bg-dark-700 border border-white/[0.06] rounded-xl px-3 py-2 text-[12px] text-white placeholder-gray-600 focus:border-accent focus:outline-none"
                placeholder="좌석, 병 구성, 대기 여부, 입장 우선 요청 등"
            >{{ old('special_request') }}</textarea>
        </div>
    </div>

    <button type="submit" class="btn-primary w-full py-3 rounded-2xl text-[13px] font-bold text-white">
        문의 접수하기
    </button>
</form>
@else
<a href="{{ route('login') }}" class="btn-primary block w-full text-center py-3 rounded-2xl text-[13px] font-bold text-white">로그인 후 문의하기</a>
@endauth
