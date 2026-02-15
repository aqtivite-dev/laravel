<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OAuth Client Credentials
    |--------------------------------------------------------------------------
    */

    'client_id' => env('AQTIVITE_CLIENT_ID', ''),

    'client_secret' => env('AQTIVITE_CLIENT_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Test Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, all API requests will be sent to the test/sandbox
    | environment instead of the production API.
    |
    */

    'test_mode' => env('AQTIVITE_TEST_MODE', false),

    /*
    |--------------------------------------------------------------------------
    | Base URL Override
    |--------------------------------------------------------------------------
    |
    | Optionally override the API base URL. Leave null to use the default
    | URL determined by the test_mode setting.
    |
    */

    'base_url' => env('AQTIVITE_BASE_URL'),

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    |
    | Supported methods: "password", "api_key", "token"
    |
    */

    'auth' => [

        'method' => env('AQTIVITE_AUTH_METHOD', 'password'),

        // Password authentication
        'username' => env('AQTIVITE_USERNAME'),
        'password' => env('AQTIVITE_PASSWORD'),

        // API key authentication
        'api_key' => env('AQTIVITE_API_KEY'),
        'api_secret' => env('AQTIVITE_API_SECRET'),

        // Token authentication
        'access_token' => env('AQTIVITE_ACCESS_TOKEN'),
        'refresh_token' => env('AQTIVITE_REFRESH_TOKEN'),

    ],

];
