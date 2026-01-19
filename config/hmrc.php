<?php

return [
    /*
    |--------------------------------------------------------------------------
    | HMRC API Environment
    |--------------------------------------------------------------------------
    |
    | The HMRC API environment to use. Options: 'sandbox' or 'production'
    | sandbox: Uses test-api.service.hmrc.gov.uk for testing
    | production: Uses api.service.hmrc.gov.uk for live data
    |
    */
    'environment' => env('HMRC_ENVIRONMENT', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | API Base URLs
    |--------------------------------------------------------------------------
    */
    'base_urls' => [
        'production' => 'https://api.service.hmrc.gov.uk',
        'sandbox' => 'https://test-api.service.hmrc.gov.uk'
    ],

    /*
    |--------------------------------------------------------------------------
    | OAuth Configuration
    |--------------------------------------------------------------------------
    |
    | Your HMRC application credentials from the Developer Hub.
    | These are used for OAuth authentication flow.
    |
    */
    'client_id' => env('HMRC_CLIENT_ID'),
    'client_secret' => env('HMRC_CLIENT_SECRET'),
    'redirect_uri' => env('HMRC_REDIRECT_URI', env('APP_URL') . '/hmrc/auth/callback'),

    /*
    |--------------------------------------------------------------------------
    | OAuth Scopes
    |--------------------------------------------------------------------------
    |
    | Combined scopes for both Income Tax (Self Assessment) and VAT.
    | Space-separated list of scopes required for your application.
    |
    */
    'scopes' => env('HMRC_SCOPES', 'read:self-assessment write:self-assessment read:vat write:vat'),

    /*
    |--------------------------------------------------------------------------
    | VAT Configuration
    |--------------------------------------------------------------------------
    */
    'vat' => [
        'vrn' => env('HMRC_VAT_VRN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Client Configuration
    |--------------------------------------------------------------------------
    */
    'timeout' => env('HMRC_TIMEOUT', 30), // API timeout in seconds
    'retry_times' => env('HMRC_RETRY_TIMES', 3), // Number of retry attempts
    'retry_delay_ms' => env('HMRC_RETRY_DELAY_MS', 200), // Initial retry delay in milliseconds
];

