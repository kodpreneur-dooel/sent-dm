<?php

return [
    'api_key' => env('SENT_DM_API_KEY'),

    'base_url' => env('SENT_DM_BASE_URL'),

    'webhook_secret' => env('SENT_DM_WEBHOOK_SECRET'),

    'max_retries' => env('SENT_DM_MAX_RETRIES', 2),

    'timeout' => env('SENT_DM_TIMEOUT', 60),
];
