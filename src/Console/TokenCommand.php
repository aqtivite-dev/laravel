<?php

namespace Aqtivite\Laravel\Console;

use Aqtivite\Laravel\Events\AuthenticationFailed;
use Aqtivite\Php\Aqtivite;
use Illuminate\Console\Command;

class TokenCommand extends Command
{
    protected $signature = 'aqtivite:token';

    protected $description = 'Obtain a fresh client credentials token from the Aqtivite API';

    public function handle(Aqtivite $client): int
    {
        $this->components->info('Requesting a token from Aqtivite API...');

        try {
            $token = $client->issueToken();

            $this->newLine();
            $this->components->info('Token obtained successfully!');
            $this->newLine();

            $this->table(
                ['Property', 'Value'],
                [
                    ['Access Token', substr($token->accessToken, 0, 30) . '...'],
                    ['Token Type', $token->tokenType],
                    ['Expires At', $token->expiresAt?->format(DATE_ATOM) ?? 'N/A'],
                    ['Scopes', implode(' ', $client->getConfig()->getScopes()) ?: 'N/A'],
                ]
            );

            $this->newLine();
            $this->components->info('Token has been stored and will be used for future API requests.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            AuthenticationFailed::dispatch($e->getMessage(), $e);
            $this->components->error('Authentication failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
