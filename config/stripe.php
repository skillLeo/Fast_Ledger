<?php
// config/services.php

return [
    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // ✅ STRIPE CONFIGURATION
    'stripe' => [
        'mode' => env('STRIPE_MODE', 'test'), // 'test' or 'live'
        
        'test' => [
            'key' => env('STRIPE_TEST_KEY'),
            'secret' => env('STRIPE_TEST_SECRET'),
        ],
        
        'live' => [
            'key' => env('STRIPE_LIVE_KEY'),
            'secret' => env('STRIPE_LIVE_SECRET'),
        ],
    ],
];