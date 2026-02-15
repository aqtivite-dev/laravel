<?php

namespace Aqtivite\Laravel;

use Aqtivite\Laravel\Contracts\TokenStoreInterface;
use Aqtivite\Laravel\Http\LaravelTransport;
use Aqtivite\Laravel\TokenStore\CacheTokenStore;
use Aqtivite\Laravel\TokenStore\FileTokenStore;
use Aqtivite\Php\Aqtivite;
use Illuminate\Support\ServiceProvider;

class AqtiviteServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/aqtivite.php', 'aqtivite');

        $this->app->singleton(Aqtivite::class, function ($app) {
            return $this->createClient($app['config']['aqtivite']);
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/aqtivite.php' => config_path('aqtivite.php'),
        ], 'aqtivite-config');
    }

    protected function createClient(array $config): Aqtivite
    {
        $client = new Aqtivite($config['client_id'], $config['client_secret']);

        $client->setTransport(new LaravelTransport);

        $this->configureEnvironment($client, $config);
        $this->configureAuth($client, $config['auth'] ?? []);
        $this->configureTokenStore($client, $config['token_store'] ?? []);

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

    protected function configureAuth(Aqtivite $client, array $auth): void
    {
        match ($auth['method'] ?? 'password') {
            'password' => $client->setAccount($auth['username'], $auth['password']),
            'api_key' => $client->setApiKey($auth['api_key'], $auth['api_secret']),
            default => null,
        };
    }

    protected function configureTokenStore(Aqtivite $client, array $config): void
    {
        $store = $this->resolveTokenStore($config);

        // Load existing token from storage
        $token = $store->get();
        if ($token) {
            $client->setToken($token->accessToken, $token->refreshToken);
        }

        // Register callback to persist tokens when they are refreshed
        $client->onTokenRefresh(function ($token) use ($store) {
            $store->put($token);
        });
    }

    protected function resolveTokenStore(array $config): TokenStoreInterface
    {
        $driver = $config['driver'] ?? 'cache';

        return match ($driver) {
            'cache' => new CacheTokenStore(
                key: $config['cache_key'] ?? 'aqtivite_token',
                ttl: $config['cache_ttl'] ?? null,
            ),
            'file' => new FileTokenStore(
                path: $config['file_path'] ?? storage_path('app/aqtivite_token.json'),
            ),
            default => $this->app->make($driver),
        };
    }
}
