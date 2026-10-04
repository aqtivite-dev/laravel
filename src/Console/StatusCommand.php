<?php

namespace Aqtivite\Laravel\Console;

use Aqtivite\Laravel\Contracts\TokenStoreInterface;
use Illuminate\Console\Command;

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
                ['Token Type', $token->tokenType],
                ['Expires At', $token->expiresAt?->format(DATE_ATOM) ?? 'N/A'],
            ]
        );

        return self::SUCCESS;
    }
}
