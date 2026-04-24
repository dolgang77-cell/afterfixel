<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = self::supportedCodes();
        $default = config('locales.default', 'ko');

        // 언어 변경 요청 처리
        if ($request->has('lang') && in_array($request->query('lang'), $supported)) {
            $locale = $request->query('lang');
            session(['locale' => $locale]);
            cookie()->queue('nite_locale', $locale, 60 * 24 * 365);

            $url = $request->url();
            $query = $request->except('lang');
            $redirect = $query ? $url . '?' . http_build_query($query) : $url;
            return redirect($redirect);
        }

        // 저장된 언어 복원: session → cookie → default
        $locale = session('locale')
            ?? $request->cookie('nite_locale')
            ?? $default;

        if (!in_array($locale, $supported)) {
            $locale = $default;
        }

        App::setLocale($locale);

        return $next($request);
    }

    /**
     * config/locales.php에서 enabled된 언어 코드 목록 반환
     */
    public static function supportedCodes(): array
    {
        return collect(config('locales.available', []))
            ->filter(fn($v) => $v['enabled'] ?? false)
            ->keys()
            ->values()
            ->toArray();
    }

    /**
     * config/locales.php에서 enabled된 언어 목록 (코드 → 설정) 반환
     * 정렬 순서 적용
     */
    public static function enabledLocales(): array
    {
        return collect(config('locales.available', []))
            ->filter(fn($v) => $v['enabled'] ?? false)
            ->sortBy('order')
            ->toArray();
    }
}
