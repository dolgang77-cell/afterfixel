<?php

namespace App\Services;

use App\Models\ForbiddenWord;
use Illuminate\Support\Facades\Cache;

class ForbiddenWordFilter
{
    /**
     * 텍스트에서 금칙어를 검사하여 결과 반환
     *
     * @return array{passed: bool, action: string|null, matched: string|null}
     */
    public static function check(string $text): array
    {
        $words = self::getActiveWords();
        $normalized = self::normalize($text);

        foreach ($words as $word) {
            $matched = match ($word->match_type) {
                'exact'    => mb_strtolower($text) === mb_strtolower($word->word),
                'contains' => str_contains($normalized, self::normalize($word->word)),
                'regex'    => (bool) @preg_match($word->word, $text),
                default    => false,
            };

            if ($matched) {
                return [
                    'passed'  => false,
                    'action'  => $word->action_type,
                    'matched' => $word->word,
                ];
            }
        }

        return ['passed' => true, 'action' => null, 'matched' => null];
    }

    /**
     * 텍스트에서 금칙어를 마스킹 처리
     */
    public static function mask(string $text): string
    {
        $words = self::getActiveWords();

        foreach ($words as $word) {
            if ($word->match_type === 'contains') {
                $replacement = str_repeat('*', mb_strlen($word->word));
                $text = str_ireplace($word->word, $replacement, $text);
            }
        }

        return $text;
    }

    private static function getActiveWords()
    {
        return Cache::remember('forbidden_words_active', 300, function () {
            return ForbiddenWord::active()->get();
        });
    }

    /**
     * 공백/특수문자 제거 정규화
     */
    private static function normalize(string $text): string
    {
        return mb_strtolower(preg_replace('/[\s\p{P}\p{S}]/u', '', $text));
    }

    public static function clearCache(): void
    {
        Cache::forget('forbidden_words_active');
    }
}
