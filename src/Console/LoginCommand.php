<?php

namespace Aqtivite\Laravel\Console;

use Aqtivite\Laravel\Events\AuthenticationFailed;
use Aqtivite\Laravel\Events\LoginSucceeded;
use Aqtivite\Php\Aqtivite;
use Illuminate\Console\Command;

class LoginCommand extends Command
{
    protected $signature = 'aqtivite:login';

    protected $description = 'Authenticate with Aqtivite API and obtain a fresh token';

    public function handle(Aqtivite $client): int
    {
        $this->components->info('Authenticating with Aqtivite API...');

        try {
            $token = $client->login();
            LoginSucceeded::dispatch($token);

            $this->newLine();
            $this->components->info('Authentication successful!');
            $this->newLine();

            $this->table(
                ['Property', 'Value'],
                [
                    ['Access Token', substr($token->accessToken, 0, 30) . '...'],
                    ['Refresh Token', $token->refreshToken ? substr($token->refreshToken, 0, 30) . '...' : 'N/A'],
                    ['Token Type', $token->tokenType ?? 'Bearer'],
                    ['Expires In', $token->expiresIn ? $token->expiresIn . ' seconds' : 'N/A'],
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
