<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoTranslator
{
    /** 요청 내 메모리 캐시 (DB 조회 최소화) */
    private static array $memoryCache = [];

    /** 미번역 텍스트 큐 (배치 처리용) */
    private static array $pendingQueue = [];

    /** 현재 요청에서 DB 캐시 프리로드 완료 여부 */
    private static bool $preloaded = false;

    /**
     * 텍스트를 현재 locale에 맞게 자동번역
     */
    public static function translate(?string $text, ?string $targetLocale = null): string
    {
        if (!$text || trim($text) === '') return '';

        $targetLocale = $targetLocale ?? app()->getLocale();
        $sourceLocale = config('locales.default', 'ko');

        if ($targetLocale === $sourceLocale) return $text;
        if (mb_strlen(trim($text)) <= 1) return $text;

        $hash = hash('sha256', $text);
        $cacheKey = "{$hash}:{$targetLocale}";

        // 1. 메모리 캐시 확인 (가장 빠름)
        if (isset(self::$memoryCache[$cacheKey])) {
            return self::$memoryCache[$cacheKey];
        }

        // 2. DB 캐시 확인
        $cached = DB::table('translation_cache')
            ->where('hash', $hash)
            ->where('target_locale', $targetLocale)
            ->value('translated_text');

        if ($cached !== null) {
            self::$memoryCache[$cacheKey] = $cached;
            return $cached;
        }

        // 3. 즉시 번역 (단건)
        $translated = self::googleTranslate($text, $sourceLocale, $targetLocale);

        if ($translated && $translated !== $text) {
            self::saveToCache($hash, $sourceLocale, $targetLocale, $text, $translated);
            self::$memoryCache[$cacheKey] = $translated;
            return $translated;
        }

        return $text;
    }

    /**
     * 여러 텍스트를 한번에 번역 (배치)
     * 페이지 렌더링 전에 미리 호출하면 개별 translate() 호출 시 캐시 히트
     */
    public static function preloadBatch(array $texts, ?string $targetLocale = null): void
    {
        $targetLocale = $targetLocale ?? app()->getLocale();
        $sourceLocale = config('locales.default', 'ko');

        if ($targetLocale === $sourceLocale) return;

        // 유효한 텍스트만 필터
        $texts = array_filter($texts, fn($t) => $t && mb_strlen(trim($t)) > 1);
        if (empty($texts)) return;

        // 해시 계산
        $hashMap = [];
        foreach ($texts as $text) {
            $hash = hash('sha256', $text);
            $cacheKey = "{$hash}:{$targetLocale}";
            if (!isset(self::$memoryCache[$cacheKey])) {
                $hashMap[$hash] = $text;
            }
        }

        if (empty($hashMap)) return;

        // DB에서 기존 캐시 일괄 조회
        $hashes = array_keys($hashMap);
        $existing = DB::table('translation_cache')
            ->whereIn('hash', $hashes)
            ->where('target_locale', $targetLocale)
            ->pluck('translated_text', 'hash')
            ->toArray();

        foreach ($existing as $hash => $translated) {
            self::$memoryCache["{$hash}:{$targetLocale}"] = $translated;
            unset($hashMap[$hash]);
        }

        // 남은 미번역 텍스트 배치 번역
        if (!empty($hashMap)) {
            $uncached = array_values($hashMap);
            $separator = "\n§§§\n";
            $combined = implode($separator, $uncached);

            // 5000자 이하면 한번에, 초과면 분할
            if (mb_strlen($combined) <= 5000) {
                $translatedCombined = self::googleTranslate($combined, $sourceLocale, $targetLocale);
                if ($translatedCombined) {
                    $parts = explode('§§§', $translatedCombined);
                    $parts = array_map('trim', $parts);

                    $hashKeys = array_keys($hashMap);
                    foreach ($hashKeys as $i => $hash) {
                        $translated = $parts[$i] ?? null;
                        if ($translated && $translated !== $hashMap[$hash]) {
                            self::saveToCache($hash, $sourceLocale, $targetLocale, $hashMap[$hash], $translated);
                            self::$memoryCache["{$hash}:{$targetLocale}"] = $translated;
                        }
                    }
                }
            } else {
                // 분할 처리
                foreach ($hashMap as $hash => $text) {
                    $translated = self::googleTranslate($text, $sourceLocale, $targetLocale);
                    if ($translated && $translated !== $text) {
                        self::saveToCache($hash, $sourceLocale, $targetLocale, $text, $translated);
                        self::$memoryCache["{$hash}:{$targetLocale}"] = $translated;
                    }
                }
            }
        }
    }

    private static function saveToCache(string $hash, string $source, string $target, string $sourceText, string $translated): void
    {
        try {
            DB::table('translation_cache')->insertOrIgnore([
                'hash'            => $hash,
                'source_locale'   => $source,
                'target_locale'   => $target,
                'source_text'     => mb_substr($sourceText, 0, 5000),
                'translated_text' => mb_substr($translated, 0, 5000),
                'created_at'      => now(),
            ]);
        } catch (\Throwable $e) {
            // 무시
        }
    }

    private static function googleTranslate(string $text, string $source, string $target): ?string
    {
        $text = mb_substr($text, 0, 5000);

        $url = 'https://translate.googleapis.com/translate_a/single?'
            . http_build_query([
                'client' => 'gtx',
                'sl'     => $source,
                'tl'     => $target,
                'dt'     => 't',
                'q'      => $text,
            ]);

        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 8,
                CURLOPT_CONNECTTIMEOUT => 3,
                CURLOPT_USERAGENT      => 'Mozilla/5.0',
                CURLOPT_SSL_VERIFYPEER => true,
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200 || !$response) return null;

            $data = json_decode($response, true);
            if (!$data || !isset($data[0])) return null;

            $result = '';
            foreach ($data[0] as $segment) {
                if (isset($segment[0])) $result .= $segment[0];
            }

            return $result ?: null;
        } catch (\Throwable $e) {
            Log::warning('AutoTranslator failed', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
