<?php

return [
    'disk' => env('PROFILE_IMAGE_DISK', 'local'),
    'base_dir' => env('PROFILE_IMAGE_BASE_DIR', 'uploads/profile'),
    'max_upload_size_kb' => (int) env('PROFILE_IMAGE_MAX_UPLOAD_KB', 10240),
    'max_dimension' => (int) env('PROFILE_IMAGE_MAX_DIMENSION', 1024),
    'thumbnail_size' => (int) env('PROFILE_IMAGE_THUMB_SIZE', 200),
    'quality' => (int) env('PROFILE_IMAGE_QUALITY', 82),
    'store_original' => (bool) env('PROFILE_IMAGE_STORE_ORIGINAL', false),

    'moderation' => [
        'provider' => env('PROFILE_IMAGE_MODERATION_PROVIDER', 'conservative'),
        'timeout_seconds' => (int) env('PROFILE_IMAGE_MODERATION_TIMEOUT', 10),
        'google_vision_api_key' => env('GOOGLE_VISION_API_KEY'),
        'thresholds' => [
            'adult' => (int) env('PROFILE_IMAGE_NSFW_ADULT_THRESHOLD', 2),
            'racy' => (int) env('PROFILE_IMAGE_NSFW_RACY_THRESHOLD', 3),
            'medical' => (int) env('PROFILE_IMAGE_NSFW_MEDICAL_THRESHOLD', 3),
            'violence' => (int) env('PROFILE_IMAGE_NSFW_VIOLENCE_THRESHOLD', 3),
        ],
        'mock_verdict' => env('PROFILE_IMAGE_MODERATION_MOCK_VERDICT', 'safe'),
    ],
];
