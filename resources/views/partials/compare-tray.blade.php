@props([
    'type',
    'items' => collect(),
    'compareUrl',
    'maxItems' => \App\Models\CompareItem::MAX_ITEMS,
])

@if($items->isNotEmpty())
    <div class="fixed inset-x-0 bottom-[72px] z-40 px-4 md:hidden">
        <div class="mx-auto max-w-lg">
            <div class="glass rounded-[1.4rem] border border-white/[0.08] p-3 shadow-card">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-[12px] font-bold text-white">{{ $items->count() }}/{{ $maxItems }} 비교중</p>
                        <p class="mt-1 text-[11px] text-gray-500">후보를 유지한 채 바로 비교 화면으로 이동합니다.</p>
                    </div>
                    <a href="{{ $compareUrl }}"
                       class="btn-primary shrink-0 rounded-2xl px-4 py-2.5 text-[12px] font-bold text-white"
                       data-track-event="list_compare_open"
                       data-track-context="{{ $type }}_tray"
                       data-track-label="비교 보기">
                        비교 보기
                    </a>
                </div>

                <div class="mt-3 flex gap-2 overflow-x-auto scrollbar-hide">
                    @foreach($items as $item)
                        <div class="flex shrink-0 items-center gap-2 rounded-2xl border border-white/[0.06] bg-dark-700/60 px-3 py-2">
                            <div class="min-w-0">
                                <p class="max-w-[120px] truncate text-[11px] font-semibold text-white">{{ $item->name }}</p>
                            </div>
                            <form action="{{ route('compare.toggle', ['type' => $type, 'id' => $item->id]) }}"
                                  method="POST"
                                  data-track-event="list_compare_add"
                                  data-track-trigger="submit"
                                  data-track-context="{{ $type }}_tray_remove"
                                  data-track-target-type="{{ $type }}"
                                  data-track-target-id="{{ $item->id }}"
                                  data-track-meta='@json(["action" => "remove"])'>
                                @csrf
                                <button type="submit" class="rounded-full bg-white/10 p-1 text-gray-400">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endif
