<?php

namespace Aqtivite\Laravel\Console;

use Aqtivite\Laravel\Contracts\TokenStoreInterface;
use Aqtivite\Laravel\Events\TokenCleared;
use Illuminate\Console\Command;

class ClearTokenCommand extends Command
{
    protected $signature = 'aqtivite:clear-token';

    protected $description = 'Clear the stored Aqtivite token';

    public function handle(TokenStoreInterface $store): int
    {
        $store->forget();
        TokenCleared::dispatch();

        $this->components->info('Aqtivite token cleared successfully.');

        return self::SUCCESS;
    }
}
