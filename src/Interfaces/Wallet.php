<?php

namespace Doinc\Wallet\Interfaces;

use Doinc\Wallet\Models\Transaction;
use Doinc\Wallet\Models\Transfer;
use Illuminate\Database\Eloquent\Relations\MorphMany;

interface Wallet
{
    /**
     * Top up the wallet with the provided amount
     *
     * @param int|float|string $amount Funds to add to the wallet
     * @param array|null $metadata Optional metadata to add to the transaction
     * @param bool $confirmed Whether the transaction was confirmed or not, defaults to confirmed
     * @return Transaction
     */
    public function deposit(int|float|string $amount, ?array $metadata = null, bool $confirmed = true): Transaction;

    /**
     * Withdraw the provided amount of funds from the wallet
     *
     * @param int|float|string $amount Funds to withdraw from the wallet
     * @param array|null $metadata Optional metadata to add to the transaction
     * @param bool $confirmed Whether the transaction was confirmed or not, defaults to confirmed
     * @return Transaction
     */
    public function withdraw(int|float|string $amount, ?array $metadata = null, bool $confirmed = true): Transaction;

    /**
     * Forcefully withdraw funds from the wallet without caring if the balance is 0 or negative
     *
     * @param int|float|string $amount Funds to withdraw from the wallet
     * @param array|null $metadata Optional metadata to add to the transaction
     * @param bool $confirmed Whether the transaction was confirmed or not, defaults to confirmed
     * @return Transaction
     */
    public function forceWithdraw(int|float|string $amount, ?array $metadata = null, bool $confirmed = true): Transaction;

    /**
     * Transfer the provided amount of funds from the wallet to the recipient address
     *
     * @param Wallet $recipient Wallet where the funds will be transferred
     * @param int|float|string $amount Funds to transfer from the wallet
     * @param array|null $metadata Optional metadata to add to the transaction
     * @return Transfer
     */
    public function transfer(self $recipient, int|float|string $amount, ?array $metadata = null): Transfer;

    /**
     * Transfer the provided amount of funds from the wallet to the recipient address without firing any exception,
     * if exception occurs silence them and returns a null object
     *
     * @param Wallet $recipient Wallet where the funds will be transferred
     * @param int|float|string $amount Funds to transfer from the wallet
     * @param array|null $metadata Optional metadata to add to the transaction
     * @return Transfer|null
     */
    public function safeTransfer(self $recipient, int|float|string $amount, ?array $metadata = null): ?Transfer;

    /**
     * Forcefully transfer the provided amount of funds from the wallet to the recipient address without caring if the
     * balance is 0 or negative
     *
     * @param Wallet $recipient Wallet where the funds will be transferred
     * @param int|float|string $amount Funds to transfer from the wallet
     * @param array|null $metadata Optional metadata to add to the transaction
     * @return Transfer
     */
    public function forceTransfer(self $recipient, int|float|string $amount, ?array $metadata = null): Transfer;

    /**
     * Check whether the balance is enough to withdraw the provided amount
     *
     * @param int|float|string $amount
     * @param bool $allow_zero
     * @return bool
     */
    public function canWithdraw(int|float|string $amount, bool $allow_zero = false): bool;

    /**
     * Get the formatted balance of the wallet
     *
     * @return string
     */
    public function getBalanceAttribute(): string;

    /**
     * Get the raw balance of the wallet
     *
     * @return string
     */
    public function getRawBalanceAttribute(): string;

    /**
     * Retrieve all the wallet transaction
     *
     * @return MorphMany
     */
    public function transactions(): MorphMany;

    /**
     * Retrieve all the wallet transfers
     *
     * @return MorphMany
     */
    public function transfers(): MorphMany;
}
