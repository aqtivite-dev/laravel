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
