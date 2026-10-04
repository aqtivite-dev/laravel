# Aqtivite Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/aqtivite/laravel.svg?style=flat-square)](https://packagist.org/packages/aqtivite/laravel)
[![Total Downloads](https://img.shields.io/packagist/dt/aqtivite/laravel.svg?style=flat-square)](https://packagist.org/packages/aqtivite/laravel)

Laravel wrapper for the [Aqtivite PHP SDK](https://github.com/aqtivite/php), providing a seamless integration with Laravel's ecosystem including automatic token management, events, Artisan commands, and more.

## Features

- 🔐 **Automatic Token Management** - Client credentials tokens are obtained, stored, and renewed before they expire
- 📦 **Laravel Integration** - Facade, helper functions, and dependency injection support
- 🎯 **Events** - Laravel events for token lifecycle (loaded, issued, expired, cleared)
- ⚡ **Artisan Commands** - CLI commands for obtaining tokens, health checks, and more
- 🔧 **Flexible Storage** - Cache or file-based token storage with custom driver support
- 🎨 **Macroable** - Extend the client with custom functionality using macros
- 🛡️ **Type Safe** - Full IDE autocomplete and type hinting support

## Requirements

- PHP 8.4 or higher
- Laravel 11.0 or higher

## Installation

Install the package via Composer:

```bash
composer require aqtivite/laravel
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=aqtivite-config
```

## Configuration

Add your Aqtivite credentials to your `.env` file:

```env
AQTIVITE_CLIENT_ID=your_client_id
AQTIVITE_CLIENT_SECRET=your_client_secret

# Scopes requested with the token, separated by spaces
AQTIVITE_SCOPES="admin:tickets:read"

# Optional: Use test environment
AQTIVITE_TEST_MODE=false

# Optional: Token storage driver (cache or file)
AQTIVITE_TOKEN_STORE=cache
```

### Configuration File

The configuration file `config/aqtivite.php` contains all available options:

```php
return [
    'client_id' => env('AQTIVITE_CLIENT_ID'),
    'client_secret' => env('AQTIVITE_CLIENT_SECRET'),
    'test_mode' => env('AQTIVITE_TEST_MODE', false),
    'base_url' => env('AQTIVITE_BASE_URL'),

    'scopes' => array_filter(explode(' ', env('AQTIVITE_SCOPES', ''))),

    'token_store' => [
        'driver' => env('AQTIVITE_TOKEN_STORE', 'cache'),
        'cache_key' => 'aqtivite_token',
        'file_path' => storage_path('app/aqtivite_token.json'),
    ],
];
```

## Usage

### Basic Usage

The client authenticates with the OAuth2 `client_credentials` grant. The token represents the client itself and carries only the scopes granted to it.

Using the helper function:

```php
$events = aqtivite()->user()->events()->get();

$event = aqtivite()->user()->events()->find('etkinlik-slug');
```

Using the Facade:

```php
use Aqtivite\Laravel\Facades\Aqtivite;

$events = Aqtivite::user()->events()->get();
```

Using dependency injection:

```php
use Aqtivite\Laravel\Aqtivite;

class EventController extends Controller
{
    public function __construct(private Aqtivite $aqtivite)
    {
    }

    public function index()
    {
        return $this->aqtivite->user()->events()->get();
    }
}
```

### Token Management

Tokens are automatically managed - you don't need to handle authentication manually:

```php
// Check if token exists
if (aqtivite()->hasStoredToken()) {
    // Token exists
}

// Get stored token
$token = aqtivite()->getStoredToken();

// Clear token (a new one is obtained on the next request)
aqtivite()->clearToken();

// Obtain a new token manually
$token = aqtivite()->issueToken();
```

## Token Storage

### Cache Driver (Default)

Stores tokens in Laravel's cache. Fast and recommended for most applications.

```env
AQTIVITE_TOKEN_STORE=cache
```

**Pros:**
- Fast
- Uses your existing cache driver (Redis, Memcached, etc.)
- Automatic expiration

**Cons:**
- Cleared when cache is flushed (`php artisan cache:clear`)

### File Driver

Stores tokens in a JSON file on disk. Useful for long-running processes.

```env
AQTIVITE_TOKEN_STORE=file
```

**Pros:**
- Persists across cache flushes
- Survives application restarts
- Useful for queue workers and scheduled tasks

**Cons:**
- Slower than cache
- Requires file write permissions

### Custom Driver

Implement your own token storage:

```php
use Aqtivite\Laravel\Contracts\TokenStoreInterface;
use Aqtivite\Php\Auth\Token;

class DatabaseTokenStore implements TokenStoreInterface
{
    public function get(): ?Token
    {
        $data = DB::table('aqtivite_tokens')->first();

        if (!$data) {
            return null;
        }

        return new Token(
            accessToken: $data->access_token,
            expiresAt: $data->expires_at ? new DateTimeImmutable($data->expires_at) : null,
            tokenType: $data->token_type,
        );
    }

    public function put(Token $token): void
    {
        DB::table('aqtivite_tokens')->updateOrInsert(
            ['id' => 1],
            [
                'access_token' => $token->accessToken,
                'token_type' => $token->tokenType,
                'expires_at' => $token->expiresAt?->format('Y-m-d H:i:s'),
                'updated_at' => now(),
            ]
        );
    }

    public function forget(): void
    {
        DB::table('aqtivite_tokens')->delete();
    }
}
```

Register in `config/aqtivite.php`:

```php
'token_store' => [
    'driver' => \App\Services\DatabaseTokenStore::class,
],
```

## Events

Listen to token lifecycle events in your `EventServiceProvider`:

```php
use Aqtivite\Laravel\Events\{
    TokenLoaded,
    TokenIssued,
    TokenExpired,
    TokenCleared,
    AuthenticationFailed,
};

protected $listen = [
    TokenIssued::class => [
        LogTokenIssued::class,
    ],

    TokenExpired::class => [
        NotifyAdminOfExpiredToken::class,
    ],

    AuthenticationFailed::class => [
        LogAuthenticationFailure::class,
        SendSlackAlert::class,
    ],
];
```

### Available Events

| Event | Description | Properties |
|-------|-------------|------------|
| `TokenLoaded` | Token loaded from storage | `$token` |
| `TokenIssued` | New token obtained from the API | `$token` |
| `TokenExpired` | Expired token detected and removed | `$token` |
| `TokenCleared` | Token manually cleared | - |
| `AuthenticationFailed` | Authentication failed | `$message`, `$exception` |

### Example Listener

```php
namespace App\Listeners;

use Aqtivite\Laravel\Events\TokenIssued;
use Illuminate\Support\Facades\Log;

class LogTokenIssued
{
    public function handle(TokenIssued $event): void
    {
        Log::info('Aqtivite token issued', [
            'expires_at' => $event->token->expiresAt?->format(DATE_ATOM),
        ]);
    }
}
```

## Artisan Commands

### Token

Obtain a new token and store it:

```bash
php artisan aqtivite:token
```

### Health Check

Check API connectivity and authentication status:

```bash
php artisan aqtivite:health
```

### Token Status

Display the current token status:

```bash
php artisan aqtivite:status
```

### Clear Token

Clear the stored token:

```bash
php artisan aqtivite:clear-token
```

## Advanced Usage

### Extending with Macros

Add custom functionality using macros:

```php
use Aqtivite\Laravel\Aqtivite;

// In a service provider
public function boot()
{
    Aqtivite::macro('admin', function () {
        return new AdminModule($this->getHttpClient());
    });
}

// Usage
aqtivite()->admin()->tickets()->find('ABC123');
```

### Example: Admin Module

```php
namespace App\Aqtivite;

use Aqtivite\Php\Modules\Module;

class AdminModule extends Module
{
    public function tickets()
    {
        return new TicketResource($this->http);
    }
}
```

### Testing

Mock the Aqtivite client in your tests:

```php
use Aqtivite\Laravel\Aqtivite;
use Aqtivite\Php\Response\ApiResponse;

public function test_event_list()
{
    $mock = $this->mock(Aqtivite::class);

    $mock->shouldReceive('user->events->get')
        ->once()
        ->andReturn(new ApiResponse(
            status: true,
            data: [
                ['id' => 1, 'name' => 'John Doe'],
                ['id' => 2, 'name' => 'Jane Smith'],
            ]
        ));

    $response = $this->get('/api/events');

    $response->assertStatus(200);
}
```

### Error Handling

All exceptions extend `Aqtivite\Php\Exceptions\AqtiviteException`:

```php
use Aqtivite\Php\Exceptions\{
    AqtiviteException,
    AuthenticationException,
    ApiException,
};

try {
    $event = aqtivite()->user()->events()->find($slug);
} catch (AuthenticationException $e) {
    // Authentication failed
    Log::error('Aqtivite auth failed: ' . $e->getMessage());
} catch (ApiException $e) {
    // API error
    Log::error('Aqtivite API error: ' . $e->getMessage());
} catch (AqtiviteException $e) {
    // General Aqtivite error
    Log::error('Aqtivite error: ' . $e->getMessage());
}
```

## API Documentation

For full API documentation, visit the [Aqtivite API Documentation](https://api.aqtivite.com.tr/docs).

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for recent changes.

## Contributing

Contributions are welcome! Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security-related issues, please email security@aqtivite.com.tr instead of using the issue tracker.

## Credits

- [Aqtivite Team](https://github.com/aqtivite)
- [All Contributors](../../contributors)

## License

This software is proprietary and confidential. The source code is publicly available for transparency and security auditing, but usage requires a valid license agreement.

**Copyright © 2024-2026 Aqtivite. All rights reserved.**

For licensing information and inquiries:
- Email: licensing@aqtivite.com.tr
- Website: https://aqtivite.com.tr

Please see [LICENSE.md](LICENSE.md) for complete terms and conditions.
