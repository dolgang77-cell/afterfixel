<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $currentDoc['title'] }} · VYBE Docs</title>
    <script src="/vendor/tailwindcss-3.4.17.js"></script>
    <style>
        .docs-body h1 { font-size: 1.875rem; line-height: 2.25rem; font-weight: 800; color: #111827; margin-top: 0; margin-bottom: 1.25rem; }
        .docs-body h2 { font-size: 1.5rem; line-height: 2rem; font-weight: 800; color: #111827; margin-top: 2rem; margin-bottom: 0.875rem; }
        .docs-body h3 { font-size: 1.25rem; line-height: 1.75rem; font-weight: 700; color: #111827; margin-top: 1.5rem; margin-bottom: 0.75rem; }
        .docs-body p, .docs-body li { font-size: 0.95rem; line-height: 1.75; color: #374151; }
        .docs-body p { margin: 0.8rem 0; }
        .docs-body ul, .docs-body ol { margin: 1rem 0; padding-left: 1.5rem; }
        .docs-body li { margin: 0.35rem 0; }
        .docs-body a { color: #2563eb; text-decoration: underline; text-underline-offset: 2px; }
        .docs-body code { background: #f3f4f6; color: #111827; border-radius: 0.375rem; padding: 0.1rem 0.35rem; font-size: 0.875rem; }
        .docs-body pre { background: #111827; color: #f9fafb; border-radius: 1rem; padding: 1rem; overflow-x: auto; margin: 1rem 0; }
        .docs-body pre code { background: transparent; color: inherit; padding: 0; }
        .docs-body blockquote { border-left: 4px solid #d1d5db; margin: 1rem 0; padding: 0.25rem 1rem; color: #4b5563; background: #f9fafb; border-radius: 0 0.75rem 0.75rem 0; }
        .docs-body table { width: 100%; border-collapse: collapse; margin: 1rem 0; font-size: 0.925rem; }
        .docs-body th, .docs-body td { border: 1px solid #e5e7eb; padding: 0.75rem; vertical-align: top; text-align: left; }
        .docs-body th { background: #f9fafb; color: #111827; font-weight: 700; }
        .docs-body hr { border: 0; border-top: 1px solid #e5e7eb; margin: 2rem 0; }
    </style>
</head>
<body class="bg-slate-100 text-slate-900">
    <div class="min-h-screen lg:grid lg:grid-cols-[320px_minmax(0,1fr)]">
        <aside class="border-b border-slate-200 bg-slate-950 px-5 py-6 text-slate-100 lg:min-h-screen lg:border-b-0 lg:border-r lg:px-6">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <a href="{{ route('docs.show') }}" class="text-lg font-black tracking-tight text-white">VYBE Docs</a>
                    <p class="mt-2 text-sm leading-6 text-slate-400">외부에서 바로 읽을 수 있는 운영 문서 모음입니다.</p>
                </div>
                <a href="/" class="rounded-full border border-white/10 px-3 py-1.5 text-xs font-semibold text-slate-300 hover:bg-white/5">서비스 홈</a>
            </div>

            <div class="mt-6 rounded-2xl border border-white/10 bg-white/5 p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">현재 문서</p>
                <p class="mt-2 text-sm font-bold text-white">{{ $currentDoc['title'] }}</p>
                <p class="mt-2 text-xs text-slate-400">{{ $currentDoc['relative_path'] }}</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ route('docs.show', ['path' => $currentDoc['relative_path'], 'raw' => 1]) }}" class="rounded-full bg-white px-3 py-1.5 text-xs font-semibold text-slate-950">원본 보기</a>
                    <a href="{{ route('docs.show') }}" class="rounded-full border border-white/10 px-3 py-1.5 text-xs font-semibold text-slate-200">문서 첫 화면</a>
                </div>
            </div>

            <nav class="mt-6 space-y-1">
                @foreach($docsNavigation as $doc)
                    <a
                        href="{{ route('docs.show', ['path' => $doc['path'] === 'index.md' ? null : $doc['path']]) }}"
                        class="block rounded-xl px-3 py-2 text-sm leading-6 {{ $currentDoc['relative_path'] === $doc['path'] ? 'bg-white text-slate-950 font-semibold' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}"
                    >
                        {{ $doc['title'] }}
                    </a>
                @endforeach
            </nav>
        </aside>

        <main class="px-4 py-6 sm:px-6 lg:px-10">
            <div class="mx-auto max-w-5xl">
                <div class="mb-4 flex flex-wrap items-center gap-2 text-sm text-slate-500">
                    <span>/docs</span>
                    @if($currentDoc['relative_path'] !== 'index.md')
                        <span>/</span>
                        <span>{{ $currentDoc['relative_path'] }}</span>
                    @endif
                    @if($currentDoc['updated_at'])
                        <span class="ml-auto text-xs">업데이트: {{ date('Y-m-d H:i', $currentDoc['updated_at']) }}</span>
                    @endif
                </div>

                <article class="docs-body rounded-[28px] border border-slate-200 bg-white px-5 py-6 shadow-sm sm:px-8 sm:py-8">
                    {!! $renderedContent !!}
                </article>
            </div>
        </main>
    </div>
</body>
</html>
