@php
    $reviewSummary = $reviewSummary ?? [
        'display_average' => null,
        'display_count' => $reviews->count(),
        'actual_count' => $reviews->count(),
        'verified_count' => 0,
        'summary_text' => null,
        'top_tags' => [],
        'count_caption' => $reviews->isNotEmpty() ? '실제 등록 후기 기준' : '첫 후기를 기다리는 중',
        'verification_caption' => '검증 배지는 같은 장소에 문의 이력이 확인된 후기입니다.',
        'recent_reviews' => $reviews->take(3),
    ];
@endphp

<div x-data="reviewComposer('{{ $reviewKey }}')" data-review-section="{{ $reviewKey }}">
    <p x-show="success" x-text="success" class="mb-3 text-[12px] text-emerald-300"></p>
    <p x-show="error" x-text="error" class="mb-3 text-[12px] text-rose-300"></p>

    <div x-ref="content" data-review-content class="space-y-3">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-[15px] font-bold flex items-center gap-2 tracking-tight">
                <span>📝</span> {{ __('review.title') }}
                <span class="text-[12px] text-gray-500 font-normal">({{ number_format($reviewSummary['display_count']) }})</span>
            </h2>
            @if(($reviewSummary['verified_count'] ?? 0) > 0)
                <span class="rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-[10px] font-semibold text-emerald-300">
                    검증 후기 {{ number_format($reviewSummary['verified_count']) }}
                </span>
            @endif
        </div>

        <div class="card overflow-hidden border border-white/[0.06] bg-[radial-gradient(circle_at_top_right,rgba(250,204,21,0.12),transparent_38%),linear-gradient(180deg,#171727_0%,#11111b_100%)] p-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-300/80">REVIEW TRUST</p>
                    @if(($reviewSummary['display_average'] ?? null) !== null)
                        <div class="mt-2 flex items-end gap-3">
                            <p class="text-[30px] font-extrabold leading-none text-white">{{ number_format((float) $reviewSummary['display_average'], 1) }}</p>
                            <div class="pb-1">
                                <x-rating-stars :rating="$reviewSummary['display_average']" :count="$reviewSummary['display_count']" size="md" />
                                <p class="mt-1 text-[11px] text-gray-500">{{ $reviewSummary['count_caption'] }}</p>
                            </div>
                        </div>
                    @else
                        <div class="mt-2">
                            <p class="text-[18px] font-bold text-white">후기 집계 준비중</p>
                            <p class="mt-1 text-[11px] text-gray-500">{{ $reviewSummary['count_caption'] }}</p>
                        </div>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-2 sm:min-w-[180px]">
                    <div class="rounded-2xl border border-white/[0.06] bg-black/20 px-3 py-3">
                        <p class="text-[10px] text-gray-500">최근 표시</p>
                        <p class="mt-1 text-[15px] font-bold text-white">{{ $reviewSummary['recent_reviews']->count() }}</p>
                        <p class="mt-1 text-[10px] text-gray-500">최신 3개 기준</p>
                    </div>
                    <div class="rounded-2xl border border-white/[0.06] bg-black/20 px-3 py-3">
                        <p class="text-[10px] text-gray-500">검증 후기</p>
                        <p class="mt-1 text-[15px] font-bold text-white">{{ number_format($reviewSummary['verified_count']) }}</p>
                        <p class="mt-1 text-[10px] text-gray-500">문의 이력 확인</p>
                    </div>
                </div>
            </div>

            @if(!empty($reviewSummary['summary_text']))
                <p class="mt-4 text-[13px] leading-[1.7] text-gray-300">{{ trans_auto($reviewSummary['summary_text']) }}</p>
            @endif

            @if(!empty($reviewSummary['top_tags']))
                <div class="mt-3 flex flex-wrap gap-1.5">
                    @foreach($reviewSummary['top_tags'] as $tag)
                        <span class="rounded-full border border-amber-500/15 bg-amber-500/10 px-2.5 py-1 text-[10px] font-semibold text-amber-200">{{ trans_auto($tag) }}</span>
                    @endforeach
                </div>
            @endif

            <p class="mt-3 text-[11px] text-gray-500">{{ $reviewSummary['verification_caption'] }}</p>

            <div class="mt-4 space-y-2.5">
                @forelse($reviewSummary['recent_reviews'] as $review)
                    <div class="rounded-2xl border border-white/[0.06] bg-dark-800/80 px-3 py-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="text-[13px] font-semibold text-gray-100">{{ $review->user?->nickname ?? $review->user?->name ?? __('common.anonymous') }}</span>
                                    @if($review->getAttribute('is_verified_review'))
                                        <span class="rounded-full bg-emerald-500/15 px-2 py-0.5 text-[10px] font-semibold text-emerald-300">검증 후기</span>
                                    @endif
                                </div>
                                <div class="mt-1 flex flex-wrap items-center gap-2">
                                    @if($review->rating)
                                        <span class="text-[11px] text-amber-400">{{ str_repeat('★', $review->rating) }}</span>
                                    @endif
                                    <span class="text-[11px] text-gray-500">{{ $review->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                            <x-report-button targetType="review" :targetId="$review->id" />
                        </div>
                        <p class="mt-2 text-[12px] leading-[1.7] text-gray-400">{{ \Illuminate\Support\Str::limit(trans_auto($review->content), 120) }}</p>
                        @if($review->tags)
                            <div class="mt-2 flex flex-wrap gap-1">
                                @foreach($review->tags as $tag)
                                    <span class="rounded-full bg-dark-700 px-2 py-0.5 text-[10px] text-gray-500">{{ trans_auto($tag) }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-white/[0.08] bg-dark-800/70 px-3 py-4 text-[12px] leading-6 text-gray-500">
                        최근 등록된 후기가 아직 없습니다. 첫 후기를 남기면 이 공간에 바로 반영됩니다.
                    </div>
                @endforelse
            </div>
        </div>

        @auth
        <form action="{{ $reviewAction }}" method="POST" class="card p-4" @submit.prevent="submit($event)">
            @csrf
            <textarea name="content" rows="2" required class="w-full bg-dark-700 border border-white/[0.06] rounded-xl px-3 py-2 text-[13px] text-white placeholder-gray-600 focus:border-accent focus:outline-none" placeholder="{{ __('review.write') }}"></textarea>
            <div class="mt-2 flex items-center justify-between gap-3">
                <select name="rating" required class="bg-dark-700 border border-white/[0.06] rounded-lg px-2 py-1 text-[12px] text-gray-400">
                    <option value="">{{ __('review.rating') }}</option>
                    @for($i = 5; $i >= 1; $i--)
                        <option value="{{ $i }}">{{ str_repeat('★', $i) }}</option>
                    @endfor
                </select>
                <button type="submit" :disabled="submitting" class="px-4 py-1.5 rounded-xl bg-accent text-white text-[12px] font-semibold disabled:opacity-60" x-text="submitting ? '등록 중...' : '{{ __('common.submit') }}'"></button>
            </div>
        </form>
        @else
        <a href="{{ route('login') }}" class="card block p-3 text-center text-[12px] text-gray-500">{{ __('review.login_write') }}</a>
        @endauth

        <div class="space-y-2">
            @if($reviews->isNotEmpty())
                <p class="text-[12px] font-semibold text-gray-400">전체 후기</p>
            @endif

            @forelse($reviews as $review)
            <div class="card p-4">
                <div class="flex justify-between items-start gap-3">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="text-[13px] font-semibold text-gray-200">{{ $review->user?->nickname ?? $review->user?->name ?? __('common.anonymous') }}</span>
                            @if($review->getAttribute('is_verified_review'))
                                <span class="rounded-full bg-emerald-500/15 px-2 py-0.5 text-[10px] font-semibold text-emerald-300">검증 후기</span>
                            @endif
                            @if($review->rating)
                                <span class="text-[11px] text-amber-400">{{ str_repeat('★', $review->rating) }}</span>
                            @endif
                            <span class="text-[11px] text-gray-600">{{ $review->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        @if(auth()->id() === $review->user_id)
                        <form action="{{ route('reviews.destroy', $review) }}" method="POST" onsubmit="return confirm('{{ __('common.confirm_delete') }}')">
                            @csrf
                            @method('DELETE')
                            <button class="text-[11px] text-gray-600 hover:text-red-400">{{ __('common.delete') }}</button>
                        </form>
                        @endif
                        <x-report-button targetType="review" :targetId="$review->id" />
                    </div>
                </div>
                <p class="text-[13px] text-gray-400 mt-1.5 leading-relaxed">{{ trans_auto($review->content) }}</p>
                @if($review->tags)
                <div class="flex flex-wrap gap-1 mt-2">
                    @foreach($review->tags as $tag)
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-dark-700 text-gray-500">{{ trans_auto($tag) }}</span>
                    @endforeach
                </div>
                @endif
            </div>
            @empty
            <div class="card p-4 text-[13px] text-gray-500">
                아직 등록된 후기가 없습니다.
            </div>
            @endforelse
        </div>
    </div>
</div>
