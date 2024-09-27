<?php

namespace LaravelTolgee\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Dusan Antonijevic\LaravelTolgee\LaravelTolgee
 */
class LaravelTolgee extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \LaravelTolgee\LaravelTolgee::class;
    }
}
