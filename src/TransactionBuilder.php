<?php

namespace Doinc\Wallet;

use Doinc\Wallet\Enums\TransactionType;
use Doinc\Wallet\Interfaces\Wallet;
use Doinc\Wallet\Models\Transaction;

class TransactionBuilder
{
    /**
     * @var array<string, mixed>
     */
    protected array $attributes = [];

    protected function __construct()
    {
        $this->attributes = [
            "payable_id" => null,
            "payable_type" => null,
            "wallet_id" => null,
            "type" => null,
            "amount" => "0",
            "confirmed" => true,
            "confirmed_at" => now(),
            "metadata" => null,
        ];
    }

    /**
     * Create a new instance of the transaction builder
     *
     * @return static
     */
    public static function init(): static
    {
        return new static();
    }

    /**
     * Define which wallet the transaction will be associated to
     *
     * @param Wallet $wallet
     * @return static
     */
    public function withWallet(Wallet $wallet): static
    {
        $this->attributes["payable_id"] = $wallet->holder->getKey();
        $this->attributes["payable_type"] = $wallet->holder->getMorphClass();
        $this->attributes["wallet_id"] = $wallet->getKey();

        return $this;
    }

    /**
     * Mark the transaction as a withdrawal transaction
     *
     * @return static
     */
    public function isWithdraw(): static
    {
        $this->attributes["type"] = TransactionType::WITHDRAW;

        return $this;
    }

    /**
     * Mark the transaction as a deposit transaction
     *
     * @return static
     */
    public function isDeposit(): static
    {
        $this->attributes["type"] = TransactionType::DEPOSIT;

        return $this;
    }

    /**
     * Define the amount the transaction refers to, by default this is 0
     *
     * @param string $amount
     * @return static
     */
    public function withAmount(string $amount): static
    {
        $this->attributes["amount"] = $amount;

        return $this;
    }

    /**
     * Mark the transaction as confirmed
     *
     * @return static
     */
    public function isConfirmed(): static
    {
        $this->attributes["confirmed"] = true;
        $this->attributes["confirmed_at"] = now();

        return $this;
    }

    /**
     * Mark the transaction as with pending confirmation
     *
     * @return static
     */
    public function isNotConfirmed(): static
    {
        $this->attributes["confirmed"] = false;
        $this->attributes["confirmed_at"] = null;

        return $this;
    }

    /**
     * Attach the provided metadata to the transaction
     *
     * @param array $metadata
     * @return static
     */
    public function withMetadata(array $metadata): static
    {
        $this->attributes["metadata"] = json_encode($metadata);

        return $this;
    }

    /**
     * Get the configured transaction model
     *
     * @return Transaction
     */
    public function get(): Transaction
    {
        return new Transaction($this->attributes);
    }
}
