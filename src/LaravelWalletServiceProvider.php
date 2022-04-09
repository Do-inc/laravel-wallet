<?php

namespace Doinc\LaravelWallet;

use Doinc\LaravelWallet\Commands\LaravelWalletCommand;
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
            ->name('laravel-wallet')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel-wallet_table')
            ->hasCommand(LaravelWalletCommand::class);
    }
}
