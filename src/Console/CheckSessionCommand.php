<?php

namespace Aqtivite\Laravel\Console;

use Aqtivite\Laravel\Contracts\TokenStoreInterface;
use Aqtivite\Php\Aqtivite;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CheckSessionCommand extends Command
{
    protected $signature = 'aqtivite:check-session';

    protected $description = 'Check current Aqtivite session and display user information';

    public function handle(Aqtivite $client, TokenStoreInterface $store): int
    {
        $this->components->info('Checking Aqtivite session...');
        $this->newLine();

        // Check stored token
        $token = $store->get();

        if (! $token) {
            $this->components->warn('No active session found.');
            $this->components->info('Use "php artisan aqtivite:login" to authenticate.');
            return self::FAILURE;
        }

        // Validate session with API
        try {
            $response = $client->me();

            if ($response->successful()) {
                $user = $response->data;

                $this->components->info('Session is active and valid.');
                $this->newLine();

                // Display user information
                $this->components->twoColumnDetail('User ID', $user['id'] ?? 'N/A');
                $this->components->twoColumnDetail('Name', $user['name'] ?? 'N/A');
                $this->components->twoColumnDetail('Email', $user['email'] ?? 'N/A');
                $this->components->twoColumnDetail('Company', $user['company']['name'] ?? 'N/A');

                $this->newLine();

                // Display token information
                $this->table(
                    ['Token Property', 'Value'],
                    [
                        ['Access Token', substr($token->accessToken, 0, 30) . '...'],
                        ['Token Type', $token->tokenType ?? 'Bearer'],
                        ['Expires In', $token->expiresIn ? $token->expiresIn . ' seconds' : 'N/A'],
                    ]
                );

                return self::SUCCESS;
            }

            $this->components->error('Session validation failed: ' . ($response->errorDescription() ?? 'Invalid session'));
            $this->components->info('Use "php artisan aqtivite:login" to re-authenticate.');

            return self::FAILURE;
        } catch (\Throwable $e) {
            $this->components->error('Failed to validate session: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
