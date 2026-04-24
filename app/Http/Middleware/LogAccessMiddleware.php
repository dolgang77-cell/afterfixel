<?php

namespace App\Http\Middleware;

use App\Models\AccessLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogAccessMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!$request->isMethod('GET')) {
            return $response;
        }

        $path = $request->path();
        if (
            $request->expectsJson() ||
            str_starts_with($path, 'api/') ||
            str_starts_with($path, '_debugbar') ||
            preg_match('/\.(css|js|png|jpg|svg|ico|woff|map)$/', $path)
        ) {
            return $response;
        }

        try {
            // 클라이언트에서 전달하는 추가 헤더/쿠키 수집
            $extra = [
                'device_id'              => $request->header('X-Device-Id') ?: $request->cookie('nite_device_id'),
                'guest_id'               => $request->cookie('nite_guest_id'),
                'device_source'          => $request->header('X-Device-Source'),
                'app_version'            => $request->header('X-App-Version'),
                'build_version'          => $request->header('X-Build-Version'),
                'client_timezone'        => $request->header('X-Client-Timezone'),
                'client_timezone_offset' => $request->header('X-Client-Timezone-Offset') ? (int) $request->header('X-Client-Timezone-Offset') : null,
                'language'               => $request->getPreferredLanguage() ? mb_substr($request->getPreferredLanguage(), 0, 10) : null,
            ];

            AccessLog::record(
                auth()->id(),
                $request->session()->getId(),
                $request->ip(),
                $request->userAgent(),
                '/' . $path,
                $request->header('referer'),
                array_filter($extra),
            );
        } catch (\Throwable $e) {
            // 로그 실패가 요청을 방해하면 안 됨
        }

        return $response;
    }
}
