<?php

namespace Aqtivite\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Aqtivite\Laravel\Aqtivite
 */
class Aqtivite extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Aqtivite\Laravel\Aqtivite::class;
    }
}
