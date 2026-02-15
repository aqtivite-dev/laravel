<?php

namespace Aqtivite\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Aqtivite\Php\Aqtivite
 */
class Aqtivite extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Aqtivite\Php\Aqtivite::class;
    }
}
