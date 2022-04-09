<?php

namespace Doinc\Wallet\Interfaces;

use Doinc\Wallet\Models\Transaction;

interface Confirmable
{
    /**
     * Confirm a pending transaction triggering the payment
     *
     * @param Transaction $transaction Transaction with pending confirmation
     * @return bool
     */
    public function confirm(Transaction $transaction): bool;

    /**
     * Confirm a pending transaction triggering the payment, if exception occurs silence them and returns false
     *
     * @param Transaction $transaction Transaction with pending confirmation
     * @return bool
     */
    public function safeConfirm(Transaction $transaction): bool;

    /**
     * Reset the confirmation status of a transaction to pending
     *
     * @param Transaction $transaction Transaction without pending confirmation
     * @return bool
     */
    public function resetConfirm(Transaction $transaction): bool;

    /**
     * Reset the confirmation status of a transaction to pending, if exception occurs silence them and returns false
     *
     * @param Transaction $transaction Transaction without pending confirmation
     * @return bool
     */
    public function safeResetConfirm(Transaction $transaction): bool;
}
