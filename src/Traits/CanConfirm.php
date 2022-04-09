<?php

namespace Doinc\Wallet\Traits;

use Doinc\Wallet\Enums\TransactionType;
use Doinc\Wallet\Events\TransactionConfirmationReset;
use Doinc\Wallet\Events\TransactionConfirmed;
use Doinc\Wallet\Exceptions\InvalidWalletModelProvided;
use Doinc\Wallet\Exceptions\InvalidWalletOwner;
use Doinc\Wallet\Exceptions\TransactionAlreadyConfirmed;
use Doinc\Wallet\Models\Transaction;
use Doinc\Wallet\Models\Wallet;
use Throwable;

trait CanConfirm
{
    /**
     * Confirm a pending transaction triggering the payment
     *
     * @param Transaction $transaction Transaction with pending confirmation
     * @return bool
     * @throws InvalidWalletModelProvided
     * @throws InvalidWalletOwner
     * @throws TransactionAlreadyConfirmed
     */
    public function confirm(Transaction $transaction): bool
    {
        if ($transaction->confirmed) {
            throw new TransactionAlreadyConfirmed();
        }

        $wallet = $this->getWallet($this);
        $this->checkWalletOwner($wallet, $transaction);

        if ($transaction->type === TransactionType::DEPOSIT) {
            $wallet->deposit($transaction->amount);
        } else {
            $wallet->withdraw($transaction->amount);
        }

        TransactionConfirmed::dispatch($wallet, $transaction);

        return $transaction->update([
            'confirmed' => true,
        ]);
    }

    /**
     * Confirm a pending transaction triggering the payment, if exception occurs silence them and returns false
     *
     * @param Transaction $transaction Transaction with pending confirmation
     * @return bool
     */
    public function safeConfirm(Transaction $transaction): bool
    {
        try {
            return $this->confirm($transaction);
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Reset the confirmation status of a transaction to pending
     *
     * @param Transaction $transaction Transaction without pending confirmation
     * @return bool
     * @throws InvalidWalletModelProvided
     * @throws InvalidWalletOwner
     */
    public function resetConfirm(Transaction $transaction): bool
    {
        if (!$transaction->confirmed) {
            return true;
        }

        $wallet = $this->getWallet($this);
        $this->checkWalletOwner($wallet, $transaction);

        TransactionConfirmationReset::dispatch($wallet, $transaction);

        return $transaction->update([
            'confirmed' => false,
        ]);
    }

    /**
     * Reset the confirmation status of a transaction to pending, if exception occurs silence them and returns false
     *
     * @param Transaction $transaction Transaction without pending confirmation
     * @return bool
     */
    public function safeResetConfirm(Transaction $transaction): bool
    {
        try {
            return $this->resetConfirm($transaction);
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Get the wallet instance from a provided object
     *
     * @param object $wallet Probable wallet instance
     * @return Wallet
     * @throws InvalidWalletModelProvided
     */
    protected function getWallet(object $wallet): Wallet
    {
        // if the provided object is not a wallet instance try retrieving the underlying wallet instance
        if (!$wallet instanceof Wallet) {
            $wallet = $wallet->wallet;
        }

        // if the wallet object still is not a wallet instance throw
        if (!$wallet instanceof Wallet) {
            throw new InvalidWalletModelProvided();
        }

        return $wallet;
    }

    /**
     * Checks if the provided wallet owns the given transaction, if it does not throw
     *
     * @param Wallet $wallet Probable wallet owner
     * @param Transaction $transaction Transaction to check for ownership
     * @return void
     * @throws InvalidWalletOwner
     */
    protected function checkWalletOwner(Wallet $wallet, Transaction $transaction) {
        if ($wallet->getKey() !== $transaction->wallet_id) {
            throw new InvalidWalletOwner();
        }
    }
}
