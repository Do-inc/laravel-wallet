<?php

namespace Doinc\Wallet\Tests;

use Doinc\Wallet\WalletServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Doinc\\Wallet\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

        Schema::dropIfExists("wallets");
        Schema::dropIfExists("transactions");
        Schema::dropIfExists("users");
        Schema::create("users", function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("email");
            $table->timestamp("email_verified_at");
            $table->string("password");
            $table->string("remember_token");
            $table->timestamps();
        });
        $migration = include __DIR__.'/../database/migrations/create_wallets_table.php.stub';
        $migration->up();
        $migration = include __DIR__.'/../database/migrations/create_transactions_table.php.stub';
        $migration->up();
    }

    protected function getPackageProviders($app): array
    {
        return [
            WalletServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.foreign_key_constraints', true);
        config()->set('database.connections.sqlite.database', __DIR__."/../database/database.sqlite");
    }
}
