<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ProfileImageModerationService
{
    private const LIKELIHOOD_MAP = [
        'UNKNOWN' => 1,
        'VERY_UNLIKELY' => 0,
        'UNLIKELY' => 1,
        'POSSIBLE' => 2,
        'LIKELY' => 3,
        'VERY_LIKELY' => 4,
    ];

    /**
     * @return array{provider:string, verdict:string, score:int, labels:array<string,int>, message:?string}
     */
    public function inspect(string $binary): array
    {
        $provider = (string) config('profile-images.moderation.provider', 'conservative');

        return match ($provider) {
            'google_vision' => $this->inspectWithGoogleVision($binary),
            'mock' => $this->inspectWithMock(),
            default => $this->conservativeFallback(),
        };
    }

    /**
     * Google Vision SafeSearch를 사용한다.
     * adult / racy / medical / violence 값을 기준으로 safe 또는 suspicious 판정을 내린다.
         *
     * @return array{provider:string, verdict:string, score:int, labels:array<string,int>, message:?string}
     */
    private function inspectWithGoogleVision(string $binary): array
    {
        $apiKey = (string) config('profile-images.moderation.google_vision_api_key');
        if ($apiKey === '') {
            return $this->conservativeFallback('Google Vision API 키가 설정되지 않았습니다.');
        }

        $response = Http::timeout((int) config('profile-images.moderation.timeout_seconds', 10))
            ->acceptJson()
            ->post('https://vision.googleapis.com/v1/images:annotate?key=' . urlencode($apiKey), [
                'requests' => [[
                    'image' => ['content' => base64_encode($binary)],
                    'features' => [
                        ['type' => 'SAFE_SEARCH_DETECTION', 'maxResults' => 1],
                    ],
                ]],
            ]);

        if (!$response->successful()) {
            return $this->conservativeFallback('Google Vision 응답 오류');
        }

        $annotation = data_get($response->json(), 'responses.0.safeSearchAnnotation');
        if (!is_array($annotation)) {
            return $this->conservativeFallback('SafeSearch 결과를 읽을 수 없습니다.');
        }

        $labels = [
            'adult' => $this->mapLikelihood((string) ($annotation['adult'] ?? 'UNKNOWN')),
            'racy' => $this->mapLikelihood((string) ($annotation['racy'] ?? 'UNKNOWN')),
            'medical' => $this->mapLikelihood((string) ($annotation['medical'] ?? 'UNKNOWN')),
            'violence' => $this->mapLikelihood((string) ($annotation['violence'] ?? 'UNKNOWN')),
        ];

        $thresholds = config('profile-images.moderation.thresholds', []);
        $score = max($labels);
        $isSuspicious = ($labels['adult'] ?? 0) >= ($thresholds['adult'] ?? 2)
            || ($labels['racy'] ?? 0) >= ($thresholds['racy'] ?? 3)
            || ($labels['medical'] ?? 0) >= ($thresholds['medical'] ?? 3)
            || ($labels['violence'] ?? 0) >= ($thresholds['violence'] ?? 3);

        return [
            'provider' => 'google_vision',
            'verdict' => $isSuspicious ? 'suspicious' : 'safe',
            'score' => $score,
            'labels' => $labels,
            'message' => null,
        ];
    }

    /**
     * @return array{provider:string, verdict:string, score:int, labels:array<string,int>, message:?string}
     */
    private function inspectWithMock(): array
    {
        $verdict = (string) config('profile-images.moderation.mock_verdict', 'safe');

        return [
            'provider' => 'mock',
            'verdict' => $verdict === 'safe' ? 'safe' : 'suspicious',
            'score' => $verdict === 'safe' ? 0 : 4,
            'labels' => [
                'adult' => $verdict === 'safe' ? 0 : 4,
                'racy' => $verdict === 'safe' ? 0 : 4,
                'medical' => 0,
                'violence' => 0,
            ],
            'message' => '테스트용 mock moderation',
        ];
    }

    /**
     * Provider 미설정 또는 오류 시 보수적으로 pending 처리한다.
     *
     * @return array{provider:string, verdict:string, score:int, labels:array<string,int>, message:?string}
     */
    private function conservativeFallback(?string $message = null): array
    {
        return [
            'provider' => 'conservative',
            'verdict' => 'suspicious',
            'score' => 1,
            'labels' => [],
            'message' => $message,
        ];
    }

    private function mapLikelihood(string $value): int
    {
        return self::LIKELIHOOD_MAP[$value] ?? 1;
    }
}
