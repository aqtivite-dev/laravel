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
    | Scopes
    |--------------------------------------------------------------------------
    |
    | Optional. Leave empty to receive every scope granted to the client on
    | the API. Set a space-separated list to narrow the token to those
    | scopes; scopes not granted to the client are not issued.
    |
    */

    'scopes' => array_filter(explode(' ', env('AQTIVITE_SCOPES', ''))),

    /*
    |--------------------------------------------------------------------------
    | Token Store
    |--------------------------------------------------------------------------
    |
    | Tokens are automatically managed during the authentication lifecycle:
    | - On boot, tokens are loaded from storage
    | - On expiry, a new token is obtained automatically
    | - New tokens are persisted back to storage
    | - When scopes are set, the cache key and file name carry a fingerprint
    |   of them, so changing the scopes obtains a new token
    |
    | Supported drivers:
    |
    | "cache" (default) - Fast, recommended for most applications
    |   - Uses Laravel's default cache driver (redis, memcached, etc.)
    |   - Tokens expire automatically based on their lifetime
    |   - Cleared when cache is flushed (php artisan cache:clear)
    |
    | "file" - Persistent storage on disk
    |   - Tokens survive cache flushes and application restarts
    |   - Useful for long-running processes (queues, scheduled tasks)
    |   - Stored as JSON with restricted permissions (0600)
    |
    | Custom - Implement your own TokenStoreInterface
    |   - Provide the fully-qualified class name
    |   - Example: \App\Services\DatabaseTokenStore::class
    |   - Must implement get(), put(), and forget() methods
    |
    | Artisan commands:
    |   php artisan aqtivite:status       - Show current token status
    |   php artisan aqtivite:clear-token  - Clear stored token
    |
    */

    'token_store' => [

        'driver' => env('AQTIVITE_TOKEN_STORE', 'cache'),

        // Cache driver: Key used to store token in cache
        'cache_key' => 'aqtivite:token',

        // File driver: Path to JSON file for token storage
        'file_path' => storage_path('app/aqtivite_token.json'),

    ],

];
