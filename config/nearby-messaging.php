<?php

return [
    'enabled' => env('NEARBY_MESSAGING_ENABLED', false),

    'location_ttl_minutes' => (int) env('NEARBY_LOCATION_TTL_MINUTES', 10),
    'message_ttl_minutes' => (int) env('NEARBY_MESSAGE_TTL_MINUTES', 30),

    'max_radius_m' => (int) env('NEARBY_MAX_RADIUS_M', 300),
    'same_venue_ttl_minutes' => (int) env('NEARBY_SAME_VENUE_TTL_MINUTES', 180),

    'list_limit' => (int) env('NEARBY_LIST_LIMIT', 40),
    'message_preview_length' => (int) env('NEARBY_MESSAGE_PREVIEW_LENGTH', 80),

    'new_conversation_limit_per_10m' => (int) env('NEARBY_NEW_CONVERSATION_LIMIT_PER_10M', 3),
    'message_limit_per_minute' => (int) env('NEARBY_MESSAGE_LIMIT_PER_MINUTE', 5),
    'message_limit_window_seconds' => (int) env('NEARBY_MESSAGE_LIMIT_WINDOW_SECONDS', 30),
];
