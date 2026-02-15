<?php

namespace Aqtivite\Laravel\Contracts;

use Aqtivite\Php\Auth\Token;

interface TokenStoreInterface
{
    /**
     * Retrieve the stored token, or null if none exists.
     */
    public function get(): ?Token;

    /**
     * Persist the given token to storage.
     */
    public function put(Token $token): void;
}
