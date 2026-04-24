<!DOCTYPE html>
<html lang="ko" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MD 대시보드') - VYBE MD</title>
    <script src="/vendor/tailwindcss-3.4.17.js"></script>
    <script defer src="/vendor/alpinejs-3.15.11.min.js"></script>
    <script>
        (() => {
            const fallbackSrc = @js(url('/images/placeholders/image-fallback.svg'));

            document.addEventListener('error', (event) => {
                const el = event.target;

                if (!(el instanceof HTMLImageElement)) {
                    return;
                }

                const nextSrc = el.dataset.fallbackSrc || fallbackSrc;

                if (!nextSrc || el.dataset.fallbackApplied === '1' || el.currentSrc === nextSrc || el.src === nextSrc) {
                    return;
                }

                el.dataset.fallbackApplied = '1';
                el.src = nextSrc;
            }, true);
        })();
    </script>
    <style>
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        .md-scroll-snap { scroll-snap-type: x proximity; }
        .md-scroll-snap > * { scroll-snap-align: start; }
        .md-editor-surface {
            min-height: 220px;
            line-height: 1.7;
        }
        .md-editor-surface:empty::before {
            content: attr(data-placeholder);
            color: rgba(148, 163, 184, 0.72);
        }
        .md-editor-surface img {
            width: 100%;
            border-radius: 1rem;
            margin: 0.9rem 0;
            border: 1px solid rgba(255,255,255,0.08);
        }
        .md-editor-surface p + p,
        .md-editor-surface p + ul,
        .md-editor-surface p + ol,
        .md-editor-surface p + blockquote,
        .md-editor-surface ul + p,
        .md-editor-surface ol + p,
        .md-editor-surface blockquote + p,
        .md-editor-surface img + p,
        .md-editor-surface p + img {
            margin-top: 0.85rem;
        }
        .md-editor-surface ul,
        .md-editor-surface ol {
            padding-left: 1.15rem;
        }
        .md-editor-surface ul { list-style: disc; }
        .md-editor-surface ol { list-style: decimal; }
        .md-editor-surface blockquote {
            border-left: 2px solid rgba(129, 140, 248, 0.65);
            padding-left: 0.85rem;
            color: rgb(203 213 225);
        }
        .md-editor-surface a {
            color: rgb(199 210 254);
            text-decoration: underline;
            text-underline-offset: 2px;
        }
        .md-editor-surface h3,
        .md-editor-surface h4 {
            margin-top: 1rem;
            color: white;
            font-weight: 800;
        }
    </style>
</head>
<body class="min-h-full bg-slate-950 text-white antialiased">
    @php
        $routeName = request()->route()->getName();
        $topNav = [
            'md-dashboard.index' => '홈',
            'md-dashboard.profile' => '프로필',
            'md-dashboard.clubs' => '클럽',
            'md-dashboard.parties' => '파티',
            'md-dashboard.inquiries' => '문의',
            'md-dashboard.reviews' => '후기',
            'md-dashboard.media' => '미디어',
        ];
        $bottomNav = [
            'md-dashboard.index' => ['label' => '홈', 'icon' => 'M3.75 9.776L12 3l8.25 6.776V20.25A.75.75 0 0119.5 21h-4.125v-5.25A.75.75 0 0014.625 15h-5.25a.75.75 0 00-.75.75V21H4.5a.75.75 0 01-.75-.75V9.776z'],
            'md-dashboard.clubs' => ['label' => '클럽', 'icon' => 'M4.5 21V8.25A2.25 2.25 0 016.75 6h10.5a2.25 2.25 0 012.25 2.25V21M9 10.5h6M9 14.25h6'],
            'md-dashboard.parties' => ['label' => '파티', 'icon' => 'M9 9l10.5-3m0 6.553v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 11-.99-3.467l2.31-.66a2.25 2.25 0 001.632-2.163zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 01-.99-3.467l2.31-.66A2.25 2.25 0 009 15.553z'],
            'md-dashboard.inquiries' => ['label' => '문의', 'icon' => 'M8.625 9.75a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m3.75 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H15.75M4.5 19.5l3.255-3.255A2.25 2.25 0 019.346 15.75H18A2.25 2.25 0 0020.25 13.5v-6A2.25 2.25 0 0018 5.25H6A2.25 2.25 0 003.75 7.5v9.75A2.25 2.25 0 006 19.5h-1.5z'],
            'md-dashboard.media' => ['label' => '미디어', 'icon' => 'M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l1.409 1.409a2.25 2.25 0 003.182 0l2.909-2.909a2.25 2.25 0 013.182 0l.545.545M3.75 3h16.5A.75.75 0 0121 3.75v16.5a.75.75 0 01-.75.75H3.75A.75.75 0 013 20.25V3.75A.75.75 0 013.75 3zm12 3.75a1.5 1.5 0 100 3 1.5 1.5 0 000-3z'],
        ];
    @endphp

    <div class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(99,102,241,0.26),_transparent_28%),linear-gradient(180deg,_#111827_0%,_#020617_100%)]">
        <header class="sticky top-0 z-40 border-b border-white/10 bg-slate-950/90 backdrop-blur">
            <div class="mx-auto flex max-w-lg items-center justify-between px-4 pb-3 pt-4">
                <div>
                    <a href="{{ route('md-dashboard.index') }}" class="text-lg font-black tracking-tight text-white">VYBE MD</a>
                    <p class="text-[11px] text-slate-400">{{ auth()->user()->name }} · 현장 모바일 관리</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('home') }}" class="rounded-full border border-white/10 bg-white/5 px-3 py-2 text-[11px] font-semibold text-slate-200">앱 보기</a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="rounded-full border border-white/10 bg-white/5 px-3 py-2 text-[11px] font-semibold text-slate-200">로그아웃</button>
                    </form>
                </div>
            </div>
            <div class="relative mx-auto max-w-lg">
                <div class="pointer-events-none absolute inset-y-0 left-0 w-6 bg-gradient-to-r from-slate-950/95 to-transparent"></div>
                <div class="pointer-events-none absolute inset-y-0 right-0 w-6 bg-gradient-to-l from-slate-950/95 to-transparent"></div>
                <div class="flex max-w-lg gap-2 overflow-x-auto scrollbar-hide px-4 pb-3 text-[12px] md-scroll-snap overscroll-x-contain [scrollbar-gutter:stable] touch-pan-x select-none">
                @foreach($topNav as $route => $label)
                    <a href="{{ route($route) }}" class="shrink-0 rounded-full px-3 py-2 font-semibold {{ str_starts_with($routeName, $route) ? 'bg-indigo-500 text-white' : 'bg-white/5 text-slate-300' }}">{{ $label }}</a>
                @endforeach
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-lg px-4 pb-28 pt-4">
            @if(session('success'))
                <div class="mb-4 rounded-2xl border border-emerald-400/20 bg-emerald-500/10 px-4 py-3 text-[13px] text-emerald-200">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-4 rounded-2xl border border-rose-400/20 bg-rose-500/10 px-4 py-3 text-[13px] text-rose-200">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-4 rounded-2xl border border-rose-400/20 bg-rose-500/10 px-4 py-3 text-[13px] text-rose-200">
                    <ul class="space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            @yield('content')
        </main>

        <nav class="fixed bottom-0 left-0 right-0 z-40 border-t border-white/10 bg-slate-950/95 backdrop-blur">
            <div class="mx-auto grid max-w-lg grid-cols-5 px-2 pb-[calc(0.5rem+env(safe-area-inset-bottom))] pt-2">
                @foreach($bottomNav as $route => $item)
                    <a href="{{ route($route) }}" class="flex flex-col items-center gap-1 rounded-2xl px-2 py-2 text-[10px] font-semibold {{ str_starts_with($routeName, $route) ? 'bg-indigo-500/15 text-indigo-200' : 'text-slate-400' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                        </svg>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </nav>
    </div>

    <script>
    function mdUploader(ownerType, ownerId) {
        return {
            uploading: false,
            error: '',
            success: '',
            parseError(payload, fallbackMessage) {
                const detailMessage = payload?.errors
                    ? Object.values(payload.errors)[0]?.[0]
                    : null;

                return detailMessage || payload?.message || fallbackMessage;
            },
            async refreshMediaGrid() {
                if (!this.$refs.mediaGrid) {
                    return false;
                }

                try {
                    const response = await fetch(window.location.href, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html',
                        },
                        cache: 'no-store',
                    });

                    if (!response.ok) {
                        return false;
                    }

                    const html = await response.text();
                    const documentFragment = new DOMParser().parseFromString(html, 'text/html');
                    const nextGrid = documentFragment.querySelector('[x-ref="mediaGrid"]');

                    if (!nextGrid) {
                        return false;
                    }

                    this.$refs.mediaGrid.innerHTML = nextGrid.innerHTML;
                    return true;
                } catch (_) {
                    return false;
                }
            },
            async uploadFiles(event) {
                const files = Array.from(event.target.files || []);
                if (!files.length) return;

                this.uploading = true;
                this.error = '';
                this.success = '';

                for (const file of files) {
                    const formData = new FormData();
                    formData.append('image', file);
                    formData.append('owner_type', ownerType);
                    formData.append('owner_id', ownerId);

                    try {
                        const response = await fetch('{{ route('md-dashboard.upload') }}', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                'Accept': 'application/json',
                            }
                        });

                        const payload = await response.json().catch(() => ({}));

                        if (!response.ok) {
                            throw new Error(this.parseError(payload, '업로드에 실패했습니다.'));
                        }

                        const refreshed = await this.refreshMediaGrid();
                        this.success = '이미지가 추가되었습니다.';

                        if (!refreshed) {
                            window.location.reload();
                            return;
                        }
                    } catch (error) {
                        this.error = error.message || '업로드 중 오류가 발생했습니다.';
                        break;
                    }
                }

                this.uploading = false;
                event.target.value = '';
            }
        };
    }

    function mdRichEditor(config) {
        return {
            uploadingInline: false,
            error: '',
            parseError(payload, fallbackMessage) {
                const detailMessage = payload?.errors
                    ? Object.values(payload.errors)[0]?.[0]
                    : null;

                return detailMessage || payload?.message || fallbackMessage;
            },
            init() {
                const current = this.$refs.input.value.trim();
                this.$refs.editor.innerHTML = current !== '' ? current : '<p><br></p>';
                this.sync();
            },
            sync() {
                const html = this.$refs.editor.innerHTML.trim();
                this.$refs.input.value = html === '<br>' ? '' : html;
            },
            focusEditor() {
                this.$refs.editor.focus();
            },
            exec(command, value = null) {
                this.focusEditor();
                document.execCommand(command, false, value);
                this.sync();
            },
            block(tag) {
                this.exec('formatBlock', '<' + tag + '>');
            },
            insertLink() {
                const url = window.prompt('링크 주소를 입력하세요');
                if (!url) return;
                this.exec('createLink', url);
            },
            insertInlineImage(url) {
                const editor = this.$refs.editor;
                const selection = window.getSelection();
                const paragraph = document.createElement('p');
                const image = document.createElement('img');
                image.src = url;
                image.alt = 'MD content image';
                paragraph.appendChild(image);

                const spacer = document.createElement('p');
                spacer.innerHTML = '<br>';

                const isSelectionInsideEditor = selection
                    && selection.rangeCount > 0
                    && editor.contains(selection.anchorNode);

                if (isSelectionInsideEditor) {
                    const range = selection.getRangeAt(0);
                    range.deleteContents();
                    range.insertNode(spacer);
                    range.insertNode(paragraph);
                    range.setStartAfter(spacer);
                    range.collapse(true);
                    selection.removeAllRanges();
                    selection.addRange(range);
                } else {
                    editor.appendChild(paragraph);
                    editor.appendChild(spacer);
                }
            },
            async uploadInlineImage(event) {
                const files = Array.from(event.target.files || []);
                if (!files.length) return;

                this.uploadingInline = true;
                this.error = '';

                for (const file of files) {
                    const formData = new FormData();
                    formData.append('image', file);
                    formData.append('owner_type', config.ownerType);
                    formData.append('owner_id', config.ownerId);
                    formData.append('upload_context', 'inline');

                    try {
                        const response = await fetch('{{ route('md-dashboard.upload') }}', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                'Accept': 'application/json',
                            }
                        });

                        const payload = await response.json().catch(() => ({}));

                        if (!response.ok || !payload.url) {
                            throw new Error(this.parseError(payload, '이미지 업로드에 실패했습니다.'));
                        }

                        this.focusEditor();
                        this.insertInlineImage(payload.url);
                        this.sync();
                    } catch (error) {
                        this.error = error.message || '이미지 업로드 중 오류가 발생했습니다.';
                        break;
                    }
                }

                this.uploadingInline = false;
                event.target.value = '';
            },
        };
    }
    </script>
    @stack('scripts')
</body>
</html>
