<?php

namespace Doinc\Wallet\Database\Factories;

use Doinc\Wallet\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Auth\User;
use Orchestra\Testbench\Factories\UserFactory;

class WalletFactory extends Factory
{
    protected $model = Wallet::class;

    public function definition(): array
    {
        return [
            "holder_type" => User::class,
            "holder_id" => UserFactory::new(),
            "name" => $this->faker->name,
            "metadata" => null,
            "balance" => "0",
            "precision" => 2,
        ];
    }

    public function withMetadata(array $metadata): WalletFactory
    {
        return $this->state(function (array $attributes) use ($metadata) {
            return [
                "metadata" => $metadata,
            ];
        });
    }

    public function withPrecision(int $precision): WalletFactory
    {
        return $this->state(function (array $attributes) use ($precision) {
            return [
                "precision" => $precision,
            ];
        });
    }

    public function withBalance(string $balance): WalletFactory
    {
        return $this->state(function (array $attributes) use ($balance) {
            return [
                "balance" => $balance,
            ];
        });
    }

    public function withHolder(User $user): WalletFactory
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                "user" => $user,
            ];
        });
    }
}

