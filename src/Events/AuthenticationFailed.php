<?php

namespace Aqtivite\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuthenticationFailed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $message,
        public readonly ?\Throwable $exception = null,
    ) {}
}
