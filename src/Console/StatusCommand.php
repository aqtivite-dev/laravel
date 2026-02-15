<?php

namespace Aqtivite\Laravel\Console;

use Aqtivite\Laravel\Contracts\TokenStoreInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class StatusCommand extends Command
{
    protected $signature = 'aqtivite:status';

    protected $description = 'Show the current Aqtivite token status';

    public function handle(TokenStoreInterface $store): int
    {
        $token = $store->get();

        if (! $token) {
            $this->components->warn('No token found in storage.');
            $this->components->info('A new token will be obtained on the next API request.');
            return self::SUCCESS;
        }

        $this->components->info('Token found in storage:');
        $this->newLine();

        $this->table(
            ['Property', 'Value'],
            [
                ['Access Token', substr($token->accessToken, 0, 20) . '...'],
                ['Refresh Token', $token->refreshToken ? substr($token->refreshToken, 0, 20) . '...' : 'N/A'],
                ['Token Type', $token->tokenType ?? 'Bearer'],
                ['Expires In', $token->expiresIn ? $token->expiresIn . ' seconds' : 'N/A'],
            ]
        );

        if ($token->expiresIn) {
            $this->newLine();
            $this->components->info('Token is valid and will be used for API requests.');
        }

        return self::SUCCESS;
    }
}
