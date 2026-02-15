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
    | Supported methods: "password", "api_key"
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

    ],

    /*
    |--------------------------------------------------------------------------
    | Token Store
    |--------------------------------------------------------------------------
    |
    | Token storage configuration. Tokens are automatically managed during
    | the authentication lifecycle. Supported drivers: "cache", "file"
    | You can also provide a custom class implementing TokenStoreInterface.
    |
    */

    'token_store' => [

        'driver' => env('AQTIVITE_TOKEN_STORE', 'cache'),

        // Cache driver options
        'cache_key' => 'aqtivite_token',

        // File driver options
        'file_path' => storage_path('app/aqtivite_token.json'),

    ],

];
