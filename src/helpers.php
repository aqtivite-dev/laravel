<?php

if (! function_exists('aqtivite')) {
    /**
     * Get the Aqtivite client instance from the container.
     */
    function aqtivite(): Aqtivite\Laravel\Aqtivite
    {
        return app(Aqtivite\Laravel\Aqtivite::class);
    }
}
