<?php

if (! function_exists('aqtivite')) {
    /**
     * Get the Aqtivite client instance from the container.
     */
    function aqtivite(): Aqtivite\Php\Aqtivite
    {
        return app(Aqtivite\Php\Aqtivite::class);
    }
}

if (! function_exists('aqtivite_forget_token')) {
    /**
     * Clear the stored Aqtivite token.
     * Forces re-authentication on the next API request.
     */
    function aqtivite_forget_token(): void
    {
        app(Aqtivite\Laravel\Contracts\TokenStoreInterface::class)->forget();
    }
}
