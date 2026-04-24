@props([
    'type',
    'filters' => [],
    'savedFilter' => null,
    'featureAvailable' => false,
    'redirectTo' => null,
])

@php
    $normalizedFilters = \App\Models\SavedFilter::normalizeFilters($type, $filters);
    $hasFilters = !empty($normalizedFilters);
    $redirectTarget = $redirectTo ?: url()->full();
@endphp

@if($featureAvailable)
    @if($savedFilter)
        <form action="{{ route('saved-filters.destroy', $savedFilter) }}"
              method="POST"
              data-track-event="saved_filter_remove"
              data-track-trigger="submit"
              data-track-context="{{ $type }}_list"
              data-track-label="{{ $savedFilter->name }}">
            @csrf
            @method('DELETE')
            <input type="hidden" name="redirect_to" value="{{ $redirectTarget }}">
            <button type="submit"
                    class="inline-flex min-h-11 items-center gap-2 rounded-2xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-2.5 text-[12px] font-semibold text-emerald-200">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                저장됨
            </button>
        </form>
    @else
        <form action="{{ route('saved-filters.store', ['type' => $type]) }}"
              method="POST"
              data-track-event="saved_filter_add"
              data-track-trigger="submit"
              data-track-context="{{ $type }}_list"
              data-track-label="{{ \App\Models\SavedFilter::labelFor($type, $normalizedFilters) }}">
            @csrf
            @foreach($normalizedFilters as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <input type="hidden" name="redirect_to" value="{{ $redirectTarget }}">
            <button type="submit"
                    class="inline-flex min-h-11 items-center gap-2 rounded-2xl border px-4 py-2.5 text-[12px] font-semibold {{ $hasFilters ? 'border-amber-500/20 bg-amber-500/10 text-amber-200' : 'border-white/[0.08] bg-dark-800 text-gray-500' }}"
                    {{ $hasFilters ? '' : 'disabled' }}>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/></svg>
                필터 저장
            </button>
        </form>
    @endif
@else
    <div class="inline-flex min-h-11 items-center gap-2 rounded-2xl border border-white/[0.08] bg-dark-800 px-4 py-2.5 text-[12px] font-semibold text-gray-500">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/></svg>
        저장 필터 준비중
    </div>
@endif
