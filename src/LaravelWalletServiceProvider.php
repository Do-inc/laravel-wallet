<?php

namespace Doinc\Wallet;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelWalletServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('doinc-wallet')
            ->hasConfigFile()
            ->hasMigrations([
                "create_wallets_table",
                "create_transactions_table",
                "create_transfers_table",
            ]);
    }
}
