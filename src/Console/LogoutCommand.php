<?php

namespace Aqtivite\Laravel\Console;

use Aqtivite\Laravel\Contracts\TokenStoreInterface;
use Aqtivite\Laravel\Events\LogoutSucceeded;
use Aqtivite\Laravel\Events\TokenCleared;
use Aqtivite\Php\Aqtivite;
use Illuminate\Console\Command;

class LogoutCommand extends Command
{
    protected $signature = 'aqtivite:logout';

    protected $description = 'Logout from Aqtivite API and clear stored token';

    public function handle(Aqtivite $client, TokenStoreInterface $store): int
    {
        $this->components->info('Logging out from Aqtivite API...');

        try {
            // Logout from API
            $response = $client->logout();

            if ($response->successful()) {
                // Clear stored token
                $store->forget();
                TokenCleared::dispatch();
                LogoutSucceeded::dispatch();

                $this->newLine();
                $this->components->info('Logout successful. Token has been cleared.');
                return self::SUCCESS;
            }

            $this->components->error('Logout failed: ' . ($response->errorDescription() ?? 'Unknown error'));
            return self::FAILURE;
        } catch (\Throwable $e) {
            $this->components->error('Logout failed: ' . $e->getMessage());

            // Clear token anyway to ensure clean state
            $store->forget();
            TokenCleared::dispatch();
            $this->components->info('Token has been cleared locally.');

            return self::FAILURE;
        }
    }
}
