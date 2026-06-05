<?php

return [
    'api_key' => env('SENT_DM_API_KEY'),

    'base_url' => env('SENT_DM_BASE_URL'),

    'webhook_secret' => env('SENT_DM_WEBHOOK_SECRET'),

    'max_retries' => env('SENT_DM_MAX_RETRIES', 2),

    'timeout' => env('SENT_DM_TIMEOUT', 60),

    'sms' => [
        'queue' => env('SENT_DM_SMS_QUEUE', 'default'),

        'sandbox' => env('SENT_DM_SMS_SANDBOX', false),

        'profile_id' => env('SENT_DM_SMS_PROFILE_ID'),

        'default_template' => [
            'id' => env('SENT_DM_SMS_TEMPLATE_ID'),
            'name' => env('SENT_DM_SMS_TEMPLATE_NAME', 'sms_notification'),
            'parameter' => env('SENT_DM_SMS_TEMPLATE_PARAMETER', 'message'),
        ],
    ],
];
