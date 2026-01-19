<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Finexer API Configuration - UPDATED FOR REAL API
    |--------------------------------------------------------------------------
    |
    | Based on actual Finexer API structure:
    | - Uses Basic Auth with API Key only (no secret needed)
    | - Single API URL (sandbox/production determined by API key)
    | - Endpoints: /vendors, /customers, /bank_accounts, /bank_transactions
    |
    */

    /*
    |--------------------------------------------------------------------------
    | API Credentials
    |--------------------------------------------------------------------------
    |
    | Your Finexer API key for authentication.
    | 
    | - Test keys start with: test_
    | - Live keys start with: live_
    | 
    | IMPORTANT: Keep secure and never commit to version control.
    |
    */
    'api_key' => env('FINEXER_API_KEY', 'test_api_key_replace_with_real'),

    /*
    |--------------------------------------------------------------------------
    | API Base URL
    |--------------------------------------------------------------------------
    |
    | Finexer API endpoint
    | Same URL for both sandbox and production (differentiated by API key)
    |
    */
    'api_url' => env('FINEXER_API_URL', 'https://api.finexer.com'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Webhook secret for validating incoming webhooks from Finexer
    |
    */
    'webhook_secret' => env('FINEXER_WEBHOOK_SECRET', 'test_webhook_secret_replace_with_real'),
    'webhook_url' => env('FINEXER_WEBHOOK_URL', env('APP_URL') . '/api/finexer/webhook'),

    /*
    |--------------------------------------------------------------------------
    | Entity Configuration
    |--------------------------------------------------------------------------
    |
    | Finexer uses "vendor" or "customer" as the main entity type.
    | Set which one your application uses.
    |
    | Options: 'vendor', 'customer'
    |
    */
    'entity_type' => env('FINEXER_ENTITY_TYPE', 'vendor'), // 'vendor' or 'customer'

    /*
    |--------------------------------------------------------------------------
    | Sync Configuration
    |--------------------------------------------------------------------------
    |
    | Transaction sync settings
    |
    */
    'sync' => [
        // How many days of historical transactions to fetch on first sync
        'initial_sync_days' => env('FINEXER_INITIAL_SYNC_DAYS', 90),

        // Auto-sync interval in hours (24 = daily)
        'auto_sync_interval_hours' => env('FINEXER_AUTO_SYNC_INTERVAL', 24),

        // Maximum number of transactions to fetch per request
        'batch_size' => env('FINEXER_BATCH_SIZE', 100),

        // Enable automatic daily sync via scheduled job
        'auto_sync_enabled' => env('FINEXER_AUTO_SYNC_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Request Configuration
    |--------------------------------------------------------------------------
    |
    | HTTP request settings
    |
    */
    'timeout' => env('FINEXER_TIMEOUT', 30), // Request timeout in seconds
    'retry' => [
        'times' => env('FINEXER_RETRY_TIMES', 3),
        'sleep' => env('FINEXER_RETRY_SLEEP', 1000), // Milliseconds between retries
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Enable detailed logging for debugging
    |
    */
    'log_requests' => env('FINEXER_LOG_REQUESTS', false),
    'log_responses' => env('FINEXER_LOG_RESPONSES', false),

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Enable/disable specific features
    |
    */
    'features' => [
        'auto_reconciliation' => env('FINEXER_AUTO_RECONCILIATION', false),
        'duplicate_detection' => env('FINEXER_DUPLICATE_DETECTION', true),
        'webhook_validation' => env('FINEXER_WEBHOOK_VALIDATION', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | ID Mappings (Optional)
    |--------------------------------------------------------------------------
    |
    | Map your database IDs to Finexer IDs if needed
    | Format: ['your_id' => 'finexer_id']
    |
    */
    'vendor_mappings' => [
        // Example: '1' => 'vnd_PvL5q9ExrdO9g3GwSNju',
    ],

    'customer_mappings' => [
        // Example: '1' => 'cus_r48apGTXopLKFtq9PLxD',
    ],
];