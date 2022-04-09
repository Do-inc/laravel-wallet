<?php

namespace Doinc\Wallet\Interfaces;

use Doinc\Wallet\Models\Transfer;

interface Customer extends Wallet
{
    /**
     * Buy the provided product for free
     *
     * @param Product $product Product to buy
     * @return Transfer
     */
    public function payFree(Product $product): Transfer;

    /**
     * Buy the provided product without firing any exception, if exception occurs silence them and returns a null
     * object
     *
     * @param Product $product Product to buy
     * @return Transfer|null
     */
    public function safePay(Product $product): ?Transfer;

    /**
     * Buy the provided product
     *
     * @param Product $product Product to buy
     * @return Transfer
     */
    public function pay(Product $product): Transfer;

    /**
     * Buy the provided product without caring if the balance is 0 or negative
     *
     * @param Product $product Product to buy
     * @return Transfer
     */
    public function forcePay(Product $product): Transfer;

    /**
     * Refund the provided product without firing any exception, if exception occurs silence them and returns false
     *
     * @param Product $product Product to refund
     * @return bool
     */
    public function safeRefund(Product $product): bool;

    /**
     * Refund the provided product
     *
     * @param Product $product Product to buy
     * @return bool
     */
    public function refund(Product $product): bool;

    /**
     * Get the payment for the provided product if it exists
     *
     * @param Product $product Product to look for
     * @return Transfer|null
     */
    public function getPayment(Product $product): ?Transfer;

    /**
     * Whether the provided product was bought
     *
     * @param Product $product Product to look for
     * @return bool
     */
    public function paid(Product $product): bool;
}
