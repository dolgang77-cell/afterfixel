@props(['targetType', 'targetId'])

@auth
<div x-data="{ open: false }">
    <button x-on:click="open = true" class="text-[11px] text-gray-600 hover:text-red-400 transition-colors">{{ __('report.btn') }}</button>

    <template x-teleport="body">
        <div x-show="open" x-cloak x-on:click.self="open = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4">
            <div class="bg-dark-800 rounded-2xl p-5 w-full max-w-sm border border-white/[0.06]" x-on:click.stop>
                <h3 class="text-[15px] font-bold text-white mb-3">{{ __('report.title') }}</h3>
                <form action="{{ route('reports.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <input type="hidden" name="target_type" value="{{ $targetType }}">
                    <input type="hidden" name="target_id" value="{{ $targetId }}">

                    <div class="space-y-2">
                        @foreach(\App\Models\Report::$reasons as $val => $label)
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="radio" name="reason" value="{{ $val }}" required class="text-accent focus:ring-accent bg-dark-700 border-gray-600">
                            <span class="text-[13px] text-gray-300">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>

                    <textarea name="detail" rows="2" class="w-full bg-dark-700 border border-white/[0.06] rounded-xl px-3 py-2 text-[12px] text-white placeholder-gray-600 focus:border-accent focus:outline-none" placeholder="{{ __('report.detail_ph') }}"></textarea>

                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 py-2.5 rounded-xl bg-red-500 text-white text-[13px] font-semibold">{{ __('report.submit') }}</button>
                        <button type="button" x-on:click="open = false" class="flex-1 py-2.5 rounded-xl bg-dark-700 text-gray-400 text-[13px] font-semibold border border-white/[0.06]">{{ __('common.cancel') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
@endauth
