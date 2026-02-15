<?php

namespace Aqtivite\Laravel\Console;

use Aqtivite\Laravel\Contracts\TokenStoreInterface;
use Aqtivite\Php\Aqtivite;
use Illuminate\Console\Command;

class HealthCommand extends Command
{
    protected $signature = 'aqtivite:health';

    protected $description = 'Check Aqtivite API health and connectivity';

    public function handle(Aqtivite $client, TokenStoreInterface $store): int
    {
        $this->components->info('Running Aqtivite health checks...');
        $this->newLine();

        $checks = [];

        // 1. Check configuration
        $config = $client->getConfig();
        $checks[] = [
            'Configuration',
            !empty($config->clientId) && !empty($config->clientSecret) ? '✓ Valid' : '✗ Invalid',
        ];

        // 2. Check token storage
        $token = $store->get();
        $checks[] = [
            'Token Storage',
            $token ? '✓ Token found' : '⚠ No token stored',
        ];

        // 3. Check API connectivity
        try {
            $response = $client->me();

            if ($response->successful()) {
                $checks[] = ['API Connectivity', '✓ Connected'];
                $checks[] = ['Authentication', '✓ Valid'];

                $user = $response->data;
                $checks[] = ['User', $user['name'] ?? $user['email'] ?? 'N/A'];
            } else {
                $checks[] = ['API Connectivity', '✗ Failed'];
                $checks[] = ['Authentication', '✗ Invalid'];
            }
        } catch (\Throwable $e) {
            $checks[] = ['API Connectivity', '✗ Failed'];
            $checks[] = ['Error', $e->getMessage()];
        }

        $this->table(['Check', 'Status'], $checks);

        // Determine overall health
        $healthy = !str_contains(json_encode($checks), '✗');

        $this->newLine();
        if ($healthy) {
            $this->components->info('All health checks passed. Aqtivite is ready to use.');
            return self::SUCCESS;
        }

        $this->components->warn('Some health checks failed. Please review the results above.');
        return self::FAILURE;
    }
}
