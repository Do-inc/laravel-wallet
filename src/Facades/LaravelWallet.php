<?php

namespace Doinc\Wallet\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Doinc\Wallet\LaravelWallet
 */
class LaravelWallet extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'doinc-wallet';
    }
}
