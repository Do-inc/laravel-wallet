<?php

namespace Doinc\LaravelWallet\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Doinc\LaravelWallet\LaravelWallet
 */
class LaravelWallet extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-wallet';
    }
}
