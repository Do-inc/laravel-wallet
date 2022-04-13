<?php

namespace Doinc\Wallet\Database\Factories;

use Doinc\Wallet\Enums\TransactionType;
use Doinc\Wallet\Models\Transaction;
use Doinc\Wallet\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            "from_type" => Wallet::class,
            "to_type" => Wallet::class,
            "from_id" => Wallet::factory(),
            "to_id" => Wallet::factory(),
            "type" => TransactionType::TRANSFER,
            "amount" => "0",
            "confirmed" => false,
            "confirmed_at" => null,
            "metadata" => null,
            "discount" => "0",
            "fee" => "0",
        ];
    }

    public function withAmount(string $amount): TransactionFactory
    {
        return $this->state(function (array $attributes) use ($amount) {
            return [
                "amount" => $amount,
            ];
        });
    }

    public function withDiscount(string $amount): TransactionFactory
    {
        return $this->state(function (array $attributes) use ($amount) {
            return [
                "discount" => $amount,
            ];
        });
    }

    public function withTax(string $amount): TransactionFactory
    {
        return $this->state(function (array $attributes) use ($amount) {
            return [
                "fee" => $amount,
            ];
        });
    }

    public function withType(TransactionType $type): TransactionFactory
    {
        return $this->state(function (array $attributes) use ($type) {
            return [
                "type" => $type,
            ];
        });
    }

    public function withMetadata(array $metadata): TransactionFactory
    {
        return $this->state(function (array $attributes) use ($metadata) {
            return [
                "metadata" => $metadata,
            ];
        });
    }

    public function confirmed(): TransactionFactory
    {
        return $this->state(function (array $attributes) {
            return [
                "confirmed" => true,
                "confirmed_at" => now(),
            ];
        });
    }

    public function unconfirmed(): TransactionFactory
    {
        return $this->state(function (array $attributes) {
            return [
                "confirmed" => false,
                "confirmed_at" => null,
            ];
        });
    }
}

