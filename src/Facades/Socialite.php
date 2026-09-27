<?php

namespace JeffersonGoncalves\Socialite\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \JeffersonGoncalves\Socialite\Socialite
 */
class Socialite extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-socialite';
    }
}
