<?php

namespace Doinc\Wallet;

use Doinc\Wallet\Models\Transaction;
use Doinc\Wallet\Observers\TransactionObserver;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class WalletServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('wallet')
            ->hasConfigFile()
            ->hasMigrations([
                "create_wallets_table",
                "create_transactions_table",
            ]);
    }

    public function bootingPackage()
    {
        Transaction::observe(TransactionObserver::class);
        parent::bootingPackage();
    }
}
