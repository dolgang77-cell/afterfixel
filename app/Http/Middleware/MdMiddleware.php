<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MdMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->isMd() || !auth()->user()->isActive()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            return redirect()->route('login')->with('error', 'MD 계정으로 로그인해주세요.');
        }

        // MD 프로필이 없으면 접근 차단
        if (!auth()->user()->mdProfile) {
            abort(403, 'MD 프로필이 연결되지 않았습니다. 관리자에게 문의하세요.');
        }

        return $next($request);
    }
}
