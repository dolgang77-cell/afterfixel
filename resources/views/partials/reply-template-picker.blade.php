@props([
    'templates' => collect(),
    'textareaId',
    'theme' => 'dark',
])

@php
    $templates = collect($templates);
    $buttonClass = $theme === 'light'
        ? 'border-gray-200 bg-gray-50 text-gray-700 hover:border-purple-300 hover:bg-purple-50 hover:text-purple-700'
        : 'border-white/10 bg-slate-900/70 text-slate-300 hover:border-indigo-300/30 hover:bg-indigo-500/10 hover:text-white';
@endphp

@if($templates->isNotEmpty())
    <div class="space-y-2">
        <div class="flex items-center justify-between gap-2">
            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] {{ $theme === 'light' ? 'text-gray-500' : 'text-slate-500' }}">빠른 템플릿</p>
            <p class="text-[10px] {{ $theme === 'light' ? 'text-gray-400' : 'text-slate-500' }}">눌러서 답변창에 삽입</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @foreach($templates as $index => $template)
                <button type="button"
                        class="rounded-full border px-3 py-1.5 text-[11px] font-semibold transition-colors {{ $buttonClass }}"
                        onclick="window.applyReplyTemplate('{{ $textareaId }}', @js($template['body']))">
                    {{ $template['title'] }}
                </button>
            @endforeach
        </div>
    </div>

    @once
        @push('scripts')
        <script>
        window.applyReplyTemplate = window.applyReplyTemplate || function (textareaId, body) {
            var textarea = document.getElementById(textareaId);
            if (!textarea) {
                return;
            }

            var current = textarea.value.trim();
            textarea.value = current ? current + "\n\n" + body : body;
            textarea.focus();
            textarea.dispatchEvent(new Event('input', { bubbles: true }));
        };
        </script>
        @endpush
    @endonce
@endif
