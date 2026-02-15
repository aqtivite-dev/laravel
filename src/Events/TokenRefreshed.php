<?php

namespace Aqtivite\Laravel\Events;

use Aqtivite\Php\Auth\Token;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TokenRefreshed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Token $token,
    ) {}
}
