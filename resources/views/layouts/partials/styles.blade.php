{{-- 글로벌 디자인 시스템 (wvybe skin — tokens only; markup & logic untouched) --}}
<style>
    /* ════════════════════════════════════════════════════════════
       Pretendard — self-hosted for intranet. 9 weights.
       ════════════════════════════════════════════════════════════ */
    @font-face { font-family: "Pretendard"; font-weight: 100; font-style: normal; font-display: swap; src: url("/fonts/Pretendard-Thin.otf") format("opentype"); }
    @font-face { font-family: "Pretendard"; font-weight: 200; font-style: normal; font-display: swap; src: url("/fonts/Pretendard-ExtraLight.otf") format("opentype"); }
    @font-face { font-family: "Pretendard"; font-weight: 300; font-style: normal; font-display: swap; src: url("/fonts/Pretendard-Light.otf") format("opentype"); }
    @font-face { font-family: "Pretendard"; font-weight: 400; font-style: normal; font-display: swap; src: url("/fonts/Pretendard-Regular.otf") format("opentype"); }
    @font-face { font-family: "Pretendard"; font-weight: 500; font-style: normal; font-display: swap; src: url("/fonts/Pretendard-Medium.otf") format("opentype"); }
    @font-face { font-family: "Pretendard"; font-weight: 600; font-style: normal; font-display: swap; src: url("/fonts/Pretendard-SemiBold.otf") format("opentype"); }
    @font-face { font-family: "Pretendard"; font-weight: 700; font-style: normal; font-display: swap; src: url("/fonts/Pretendard-Bold.otf") format("opentype"); }
    @font-face { font-family: "Pretendard"; font-weight: 800; font-style: normal; font-display: swap; src: url("/fonts/Pretendard-ExtraBold.otf") format("opentype"); }
    @font-face { font-family: "Pretendard"; font-weight: 900; font-style: normal; font-display: swap; src: url("/fonts/Pretendard-Black.otf") format("opentype"); }

    /* ════════════════════════════════════════════════════════════
       wvybe tokens — dark stage, violet + cyan
       (from 정보/wvybe Design System1/colors_and_type.css)
       ════════════════════════════════════════════════════════════ */
    :root {
        /* ink (surfaces) */
        --wv-ink-0:  #07070A;
        --wv-ink-10: #0D0D15;
        --wv-ink-20: #1C1E30;
        --wv-ink-30: #282941;
        --wv-ink-40: #2D2D4B;
        --wv-ink-50: #383852;
        --wv-ink-60: #404162;
        --wv-ink-70: #807EA6;
        --wv-ink-80: #9A9BD1;
        --wv-ink-90: #C9CDD2;
        --wv-ink-95: #E4E3EF;
        /* brand */
        --wv-violet:    #5D5EFC;
        --wv-violet-2:  #6061FF;
        --wv-violet-d:  #3535B0;
        --wv-cyan:      #38D7ED;
        /* alpha */
        --wv-glass:     rgba(255,255,255,0.12);
        --wv-glass-hi:  rgba(255,255,255,0.16);
        --wv-grid:      rgba(255,255,255,0.04);
        /* shadows */
        --wv-glass-shadow: inset 0 1px 0 rgba(255,255,255,0.06), 0 24px 48px 0 rgba(0,0,0,0.35);
        --wv-glow-violet:  0 0 0 1px rgba(96,97,255,0.4), 0 20px 60px 0 rgba(93,94,252,0.35);
        /* motion */
        --wv-ease: cubic-bezier(0.22, 1, 0.36, 1);
    }

    /* ── Base ── */
    html {
        background: var(--wv-ink-0);
        -webkit-tap-highlight-color: transparent;
        color-scheme: dark;
    }
    body {
        font-family: "Pretendard", -apple-system, BlinkMacSystemFont, system-ui, sans-serif;
        padding-bottom: env(safe-area-inset-bottom);
        letter-spacing: -0.005em;
    }
    * { -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }
    [x-cloak] { display: none !important; }

    /* ── Layout spacing ── */
    .tab-bar-height { height: calc(68px + env(safe-area-inset-bottom)); }
    .content-pb { padding-bottom: calc(80px + env(safe-area-inset-bottom)); }
    .safe-bottom { padding-bottom: max(12px, env(safe-area-inset-bottom)); }

    /* ── PWA standalone: 상단 safe area 대응 ── */
    @media all and (display-mode: standalone) {
        body { padding-top: env(safe-area-inset-top); }
    }

    /* ── Scrollbar ── */
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

    /* ── Rich content ── */
    .rich-content p + p,
    .rich-content ul + p,
    .rich-content ol + p,
    .rich-content blockquote + p,
    .rich-content p + ul,
    .rich-content p + ol,
    .rich-content p + blockquote,
    .rich-content img + p,
    .rich-content p + img {
        margin-top: 0.85rem;
    }
    .rich-content ul,
    .rich-content ol { padding-left: 1.1rem; }
    .rich-content ul { list-style: disc; }
    .rich-content ol { list-style: decimal; }
    .rich-content blockquote {
        border-left: 2px solid rgba(93, 94, 252, 0.55);
        padding-left: 0.85rem;
        color: var(--wv-ink-90);
    }
    .rich-content a {
        color: var(--wv-cyan);
        text-decoration: underline;
        text-underline-offset: 2px;
    }
    .rich-content h3,
    .rich-content h4 {
        color: #F0F0FB;
        font-weight: 800;
        margin-top: 1rem;
        letter-spacing: -0.01em;
    }
    .rich-content h3 { font-size: 1rem; }
    .rich-content h4 { font-size: 0.92rem; }
    .rich-content img {
        width: 100%;
        border-radius: 1rem;
        margin-top: 0.9rem;
        border: 1px solid rgba(255,255,255,0.06);
    }

    /* ── Glass morphism (헤더·탭바에 그대로 적용됨) ── */
    .glass {
        background: rgba(13, 13, 21, 0.72);
        backdrop-filter: saturate(160%) blur(24px);
        -webkit-backdrop-filter: saturate(160%) blur(24px);
    }
    .glass-light {
        background: rgba(28, 30, 48, 0.6);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
    }

    /* ── Card system ──
       wvybe: bg #1C1E30, border 2px #404162, radius 16, inset highlight */
    .card {
        background: var(--wv-ink-20);
        border: 1.5px solid var(--wv-ink-60);
        border-radius: 16px;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.04);
        transition: box-shadow 0.22s var(--wv-ease),
                    border-color 0.22s var(--wv-ease),
                    transform 0.22s var(--wv-ease);
    }
    .card:active { transform: translateY(1px); }
    @media (hover: hover) {
        .card:hover {
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.06),
                        0 24px 48px -8px rgba(0,0,0,0.45),
                        0 0 0 1px rgba(96,97,255,0.25);
            border-color: rgba(96,97,255,0.35);
        }
    }

    /* ── Typography (text gradients) — remapped to violet + cyan ── */
    .gradient-text {
        background: linear-gradient(135deg, #6061FF 0%, #5D5EFC 45%, #38D7ED 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .gradient-text-simple {
        background: linear-gradient(135deg, #6061FF, #38D7ED);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    /* ── Brand surface gradients (remapped to violet + cyan) ── */
    .gradient-purple { background: linear-gradient(135deg, #3535B0, #5D5EFC); }
    .gradient-pink   { background: linear-gradient(135deg, #5D5EFC, #38D7ED); }
    .gradient-accent { background: linear-gradient(135deg, #5D5EFC 0%, #6061FF 100%); }
    .gradient-warm   { background: linear-gradient(135deg, #5D5EFC 0%, #6061FF 50%, #38D7ED 100%); }

    /* ── CTA buttons — 클래스 이름·문구·형태 그대로, 스킨만 교체 ── */
    .btn-primary {
        background: var(--wv-violet);
        color: #fff;
        box-shadow: 0 8px 24px -6px rgba(93, 94, 252, 0.55),
                    inset 0 1px 0 rgba(255,255,255,0.12);
        transition: background 0.22s var(--wv-ease),
                    box-shadow 0.22s var(--wv-ease),
                    transform 0.15s var(--wv-ease);
    }
    @media (hover: hover) {
        .btn-primary:hover {
            background: var(--wv-violet-2);
            box-shadow: var(--wv-glow-violet);
        }
    }
    .btn-primary:active {
        background: var(--wv-violet-d);
        transform: translateY(1px);
        box-shadow: 0 2px 10px -4px rgba(93, 94, 252, 0.5);
    }
    .btn-secondary {
        background: rgba(93, 94, 252, 0.10);
        border: 1px solid rgba(93, 94, 252, 0.32);
        color: #A5A7FF;
        transition: background 0.22s var(--wv-ease), transform 0.15s var(--wv-ease);
    }
    .btn-secondary:active {
        transform: translateY(1px);
        background: rgba(93, 94, 252, 0.18);
    }

    /* ── Tab bar ── */
    .tab-item {
        color: rgba(128, 126, 166, 0.7);
        transition: color 0.22s var(--wv-ease);
        position: relative;
    }
    .tab-item:active { transform: scale(0.94); }
    .tab-active { color: var(--wv-violet-2) !important; }
    .tab-active::before {
        content: '';
        position: absolute;
        top: -1px;
        left: 50%;
        transform: translateX(-50%);
        width: 22px;
        height: 2px;
        background: linear-gradient(90deg, var(--wv-violet), var(--wv-cyan));
        border-radius: 2px;
        box-shadow: 0 0 12px rgba(93, 94, 252, 0.6);
    }

    /* ── Animations ── */
    @keyframes shimmer {
        0%   { background-position: -200% 0; }
        100% { background-position:  200% 0; }
    }
    .skeleton {
        background: linear-gradient(90deg, var(--wv-ink-20) 25%, var(--wv-ink-30) 50%, var(--wv-ink-20) 75%);
        background-size: 200% 100%;
        animation: shimmer 1.5s ease-in-out infinite;
        border-radius: 0.5rem;
    }

    @keyframes fade-up {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-up {
        animation: fade-up 0.42s var(--wv-ease) forwards;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50%      { transform: translateY(-4px); }
    }
    .animate-float { animation: float 3s ease-in-out infinite; }

    @keyframes glow-pulse {
        0%, 100% { box-shadow: 0 0 8px rgba(93,94,252,0.35); }
        50%      { box-shadow: 0 0 24px rgba(93,94,252,0.7); }
    }
    .animate-glow { animation: glow-pulse 2s ease-in-out infinite; }

    /* ── Image overlay system ── */
    .img-overlay::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, var(--wv-ink-0) 0%, transparent 60%);
        pointer-events: none;
    }

    /* ── Line clamp ── */
    .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .line-clamp-3 { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }

    /* ── Input focus ring — violet ── */
    input:focus, select:focus, textarea:focus {
        outline: none;
        border-color: rgba(96, 97, 255, 0.6) !important;
        box-shadow: 0 0 0 3px rgba(96, 97, 255, 0.18);
    }

    /* ── Snap scroll ── */
    .snap-x { scroll-snap-type: x mandatory; }
    .snap-start { scroll-snap-align: start; }

    /* ── 4% grid backdrop (Clavius signature) ── */
    .bg-grid {
        background-image:
            linear-gradient(var(--wv-grid) 1px, transparent 1px),
            linear-gradient(90deg, var(--wv-grid) 1px, transparent 1px);
        background-size: 160px 160px;
    }

    /* ── Selection color ── */
    ::selection { background: rgba(93, 94, 252, 0.35); color: #fff; }
</style>
