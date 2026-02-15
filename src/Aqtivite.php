<?php

namespace Aqtivite\Laravel;

use Aqtivite\Laravel\Contracts\TokenStoreInterface;
use Aqtivite\Laravel\Events\TokenCleared;
use Aqtivite\Php\Auth\Token;
use Aqtivite\Php\Concerns\Macroable;

/**
 * Laravel wrapper for Aqtivite PHP SDK.
 *
 * Extends the base SDK with Laravel-specific features and supports macros
 * for adding custom functionality.
 *
 * @example Adding custom modules via macros
 * ```php
 * use Aqtivite\Laravel\Aqtivite;
 * use App\Aqtivite\AdminModule;
 *
 * // Register macro in a service provider
 * Aqtivite::macro('admin', function () {
 *     return new AdminModule($this->getHttpClient());
 * });
 *
 * // Usage
 * aqtivite()->admin()->users()->list();
 * ```
 */
class Aqtivite extends \Aqtivite\Php\Aqtivite
{
    use Macroable;

    public function __construct(
        string $clientId,
        string $clientSecret,
        protected TokenStoreInterface $tokenStore,
    ) {
        parent::__construct($clientId, $clientSecret);
    }

    /**
     * Clear the stored token and force re-authentication on next request.
     */
    public function clearToken(): void
    {
        $this->tokenStore->forget();
        TokenCleared::dispatch();
    }

    /**
     * Get the stored token without validating it with the API.
     */
    public function getStoredToken(): ?Token
    {
        return $this->tokenStore->get();
    }

    /**
     * Check if a token exists in storage.
     */
    public function hasStoredToken(): bool
    {
        return $this->tokenStore->get() !== null;
    }
}
