<?php

namespace Aqtivite\Laravel;

use Aqtivite\Laravel\Console\ClearTokenCommand;
use Aqtivite\Laravel\Console\HealthCommand;
use Aqtivite\Laravel\Console\StatusCommand;
use Aqtivite\Laravel\Console\TokenCommand;
use Aqtivite\Laravel\Contracts\TokenStoreInterface;
use Aqtivite\Laravel\Events\TokenLoaded;
use Aqtivite\Laravel\Events\TokenIssued;
use Aqtivite\Laravel\Http\LaravelTransport;
use Aqtivite\Laravel\TokenStore\CacheTokenStore;
use Aqtivite\Laravel\TokenStore\FileTokenStore;
use Illuminate\Support\ServiceProvider;
use Aqtivite\Php\Aqtivite as BaseAqtivite;

class AqtiviteServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/aqtivite.php', 'aqtivite');

        $this->app->singleton(TokenStoreInterface::class, function ($app) {
            return $this->resolveTokenStore($app['config']['aqtivite.token_store'] ?? []);
        });

        $this->app->singleton(Aqtivite::class, function ($app) {
            return $this->createClient($app['config']['aqtivite']);
        });

        // Bind base class to our extended implementation
        $this->app->singleton(BaseAqtivite::class, function ($app) {
            return $app->make(Aqtivite::class);
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/aqtivite.php' => config_path('aqtivite.php'),
        ], 'aqtivite-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                TokenCommand::class,
                HealthCommand::class,
                StatusCommand::class,
                ClearTokenCommand::class,
            ]);
        }
    }

    protected function createClient(array $config): Aqtivite
    {
        $tokenStore = $this->app->make(TokenStoreInterface::class);

        $client = new Aqtivite(
            clientId: $config['client_id'],
            clientSecret: $config['client_secret'],
            tokenStore: $tokenStore,
            scopes: $config['scopes'] ?? [],
        );

        $client->setTransport(new LaravelTransport);

        $this->configureEnvironment($client, $config);
        $this->configureTokenStore($client, $tokenStore);

        return $client;
    }

    protected function configureEnvironment(Aqtivite $client, array $config): void
    {
        if ($config['test_mode']) {
            $client->testMode();
        }

        if (! empty($config['base_url'])) {
            $client->setBaseUrl($config['base_url']);
        }
    }

    protected function configureTokenStore(Aqtivite $client, TokenStoreInterface $store): void
    {
        // Load existing token from storage
        try {
            $token = $store->get();
            if ($token) {
                $client->setToken($token->accessToken, $token->expiresAt);
                TokenLoaded::dispatch($token);
            }
        } catch (\Throwable $e) {
            // Token store unavailable, will authenticate with credentials on first request
            report($e);
        }

        // Register callback to persist tokens when they are issued
        $client->onTokenIssued(function ($token) use ($store) {
            try {
                $store->put($token);
                TokenIssued::dispatch($token);
            } catch (\Throwable $e) {
                // Failed to persist token, but continue execution
                report($e);
            }
        });
    }

    protected function resolveTokenStore(array $config): TokenStoreInterface
    {
        $driver = $config['driver'] ?? 'cache';

        return match ($driver) {
            'cache' => new CacheTokenStore(
                key: $config['cache_key'] ?? 'aqtivite_token',
            ),
            'file' => new FileTokenStore(
                path: $config['file_path'] ?? storage_path('app/aqtivite_token.json'),
            ),
            default => $this->app->make($driver),
        };
    }
}
