<?php

namespace Doinc\Wallet\Interfaces;

use Doinc\Wallet\Models\Transaction;
use Illuminate\Support\Collection;

interface Customer extends Wallet
{
    /**
     * Buy the provided product for free
     *
     * @param Product $product Product to buy
     * @return Transaction
     */
    public function payFree(Product $product): Transaction;

    /**
     * Buy the provided product without firing any exception, if exception occurs silence them and returns a null
     * object
     *
     * @param Product $product Product to buy
     * @return Transaction|null
     */
    public function safePay(Product $product): ?Transaction;

    /**
     * Buy the provided product
     *
     * @param Product $product Product to buy
     * @return Transaction
     */
    public function pay(Product $product): Transaction;

    /**
     * Buy the provided product without caring if the balance is 0 or negative
     *
     * @param Product $product Product to buy
     * @return Transaction
     */
    public function forcePay(Product $product): Transaction;

    /**
     * Refund the provided product without firing any exception, if exception occurs silence them and returns false
     *
     * @param Product $product Product to refund
     * @return bool
     */
    public function safeRefund(Product $product): ?Transaction;

    /**
     * Refund the provided product
     *
     * @param Product $product Product to buy
     * @return bool
     */
    public function refund(Product $product): Transaction;

    /**
     * Get the payment for the provided product if it exists
     *
     * @param Product $product Product to look for
     * @return Transaction|null
     */
    public function getPayment(Product $product): ?Transaction;

    /**
     * Get all the payments for the provided product if they exists
     *
     * @param Product $product Product to look for
     * @return Collection<Transaction>
     */
    public function getAllPayments(Product $product): Collection;

    /**
     * Whether the provided product was bought
     *
     * @param Product $product Product to look for
     * @return bool
     */
    public function paid(Product $product): bool;
}
