<!DOCTYPE html>
<html lang="ko" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#07070A">
    <title>오프라인 - VYBE</title>
    <style>
        @font-face { font-family: "Pretendard"; font-weight: 400; src: url("/fonts/Pretendard-Regular.otf") format("opentype"); font-display: swap; }
        @font-face { font-family: "Pretendard"; font-weight: 700; src: url("/fonts/Pretendard-Bold.otf") format("opentype"); font-display: swap; }
        @font-face { font-family: "Pretendard"; font-weight: 800; src: url("/fonts/Pretendard-ExtraBold.otf") format("opentype"); font-display: swap; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #07070A;
            color: #E4E3EF;
            font-family: "Pretendard", -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem;
            letter-spacing: -0.005em;
        }
        .container { max-width: 320px; }
        .icon {
            width: 80px; height: 80px;
            margin: 0 auto 1.5rem;
            border-radius: 1.25rem;
            background: linear-gradient(135deg, #5D5EFC, #38D7ED);
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem; font-weight: 900; color: white;
            opacity: 0.9;
            box-shadow: 0 20px 60px 0 rgba(93, 94, 252, 0.35);
        }
        h1 { font-size: 1.25rem; font-weight: 800; margin-bottom: 0.75rem; letter-spacing: -0.01em; }
        p { font-size: 0.875rem; color: #807EA6; line-height: 1.6; margin-bottom: 2rem; }
        .btn {
            display: inline-block;
            padding: 0.75rem 2rem;
            background: #5D5EFC;
            color: white;
            font-size: 0.875rem;
            font-weight: 700;
            border: none;
            border-radius: 1rem;
            cursor: pointer;
            text-decoration: none;
            box-shadow: 0 8px 24px -6px rgba(93, 94, 252, 0.55),
                        inset 0 1px 0 rgba(255,255,255,0.12);
        }
        .btn:active { transform: translateY(1px); background: #3535B0; }
        .signal {
            margin-top: 2rem;
            font-size: 0.75rem;
            color: #383852;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">N</div>
        <h1>오프라인 상태입니다</h1>
        <p>인터넷 연결이 필요합니다.<br>네트워크 연결을 확인한 후 다시 시도해 주세요.</p>
        <button class="btn" onclick="location.reload()">다시 시도</button>
        <p class="signal">연결이 복구되면 자동으로 돌아옵니다</p>
    </div>
    <script>
        window.addEventListener('online', () => location.reload());
    </script>
</body>
</html>
