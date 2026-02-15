<?php

namespace Aqtivite\Laravel;

use Aqtivite\Laravel\Http\LaravelTransport;
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
            'token' => $client->setToken($auth['access_token'], $auth['refresh_token']),
            default => null,
        };
    }
}
