<?php

$debugUiEnabled = filter_var(
    env('MAX_DEBUG_UI_ENABLED', true),
    FILTER_VALIDATE_BOOL,
    FILTER_NULL_ON_FAILURE,
);

return [
    'api' => [
        'base_url' => rtrim((string) env('MAX_BOT_API_BASE_URL', 'https://platform-api.max.ru'), '/'),
        'token' => env('MAX_BOT_TOKEN'),
        'timeout' => (int) env('MAX_BOT_TIMEOUT', 10),
        'connect_timeout' => (int) env('MAX_BOT_CONNECT_TIMEOUT', 5),
    ],

    'webhook' => [
        'url' => env('MAX_WEBHOOK_URL'),
        'secret' => env('MAX_WEBHOOK_SECRET'),
        'update_types' => array_values(array_filter(array_map(
            static fn (string $value): string => trim($value),
            explode(',', (string) env('MAX_WEBHOOK_UPDATE_TYPES', 'message_created,message_callback,user_added,bot_started'))
        ))),
        'storage_disk' => env('MAX_WEBHOOK_STORAGE_DISK', 'local'),
        'storage_path' => trim((string) env('MAX_WEBHOOK_STORAGE_PATH', 'max/webhooks'), '/'),
        'history_limit' => (int) env('MAX_WEBHOOK_HISTORY_LIMIT', 50),
    ],

    'debug_ui' => [
        'enabled' => $debugUiEnabled ?? true,
    ],
];
