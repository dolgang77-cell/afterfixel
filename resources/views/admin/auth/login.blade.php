<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>관리자 로그인 - VYBE Admin</title>
    <script src="/vendor/tailwindcss-3.4.17.js"></script>
</head>
<body class="bg-gray-900 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-sm">
        <div class="bg-gray-800 rounded-2xl shadow-xl p-8">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-white tracking-wider">VYBE</h1>
                <p class="text-gray-400 text-sm mt-1">관리자 로그인</p>
            </div>

            @if($errors->any())
                <div class="mb-4 p-3 bg-red-900/50 border border-red-700 text-red-300 rounded-lg text-sm">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm text-gray-300 mb-1">이메일</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm text-gray-300 mb-1">비밀번호</label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500">
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="remember" id="remember" class="rounded border-gray-600 bg-gray-700 text-purple-500 focus:ring-purple-500">
                    <label for="remember" class="ml-2 text-sm text-gray-400">로그인 유지</label>
                </div>
                <button type="submit" class="w-full py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition">
                    로그인
                </button>
            </form>
        </div>
    </div>
</body>
</html>
