<?php

namespace Doinc\Wallet\Interfaces;

use Doinc\Wallet\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;

interface Wallet
{
    /**
     * Top up the wallet with the provided amount
     *
     * @param int|float|string $amount Funds to add to the wallet
     * @param array $metadata Optional metadata to add to the transaction
     * @param bool $confirmed Whether the transaction was confirmed or not, defaults to confirmed
     * @return Transaction
     */
    public function deposit(int|float|string $amount, array $metadata = [], bool $confirmed = true): Transaction;

    /**
     * Withdraw the provided amount of funds from the wallet
     *
     * @param int|float|string $amount Funds to withdraw from the wallet
     * @param array $metadata Optional metadata to add to the transaction
     * @param bool $confirmed Whether the transaction was confirmed or not, defaults to confirmed
     * @return Transaction
     */
    public function withdraw(int|float|string $amount, array $metadata = [], bool $confirmed = true): Transaction;

    /**
     * Forcefully withdraw funds from the wallet without caring if the balance is 0 or negative
     *
     * @param int|float|string $amount Funds to withdraw from the wallet
     * @param array $metadata Optional metadata to add to the transaction
     * @return Transaction
     */
    public function forceWithdraw(int|float|string $amount, array $metadata = []): Transaction;

    /**
     * Transfer the provided amount of funds from the wallet to the recipient address
     *
     * @param Wallet $recipient Wallet where the funds will be transferred
     * @param int|float|string $amount Funds to transfer from the wallet
     * @param array $metadata Optional metadata to add to the transaction
     * @param bool $confirmed Whether the transaction was confirmed or not, defaults to confirmed
     * @return Transaction
     */
    public function transfer(Wallet $recipient, int|float|string $amount, array $metadata = [], bool $confirmed = true): Transaction;

    /**
     * Transfer the provided amount of funds from the wallet to the recipient address without firing any exception,
     * if exception occurs silence them and returns a null object
     *
     * @param Wallet $recipient Wallet where the funds will be transferred
     * @param int|float|string $amount Funds to transfer from the wallet
     * @param array $metadata Optional metadata to add to the transaction
     * @param bool $confirmed Whether the transaction was confirmed or not, defaults to confirmed
     * @return Transaction|null
     */
    public function safeTransfer(Wallet $recipient, int|float|string $amount, array $metadata = [], bool $confirmed = true): ?Transaction;

    /**
     * Forcefully transfer the provided amount of funds from the wallet to the recipient address without caring if the
     * balance is 0 or negative
     *
     * @param Wallet $recipient Wallet where the funds will be transferred
     * @param int|float|string $amount Funds to transfer from the wallet
     * @param array $metadata Optional metadata to add to the transaction
     * @return Transaction
     */
    public function forceTransfer(Wallet $recipient, int|float|string $amount, array $metadata = []): Transaction;

    /**
     * Check whether the balance is enough to withdraw the provided amount
     *
     * @param int|float|string $amount
     * @param bool $allow_zero
     * @return bool
     */
    public function canWithdraw(int|float|string $amount, bool $allow_zero = false): bool;

    /**
     * Retrieve all the wallet transaction
     *
     * @return Builder
     */
    public function transactions(): Builder;

    /**
     * Retrieve all the sent transaction
     *
     * @return MorphMany
     */
    public function sentTransactions(): MorphMany;

    /**
     * Retrieve all the received transaction
     *
     * @return MorphMany
     */
    public function receivedTransactions(): MorphMany;
}
