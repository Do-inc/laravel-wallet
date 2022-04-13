<?php

namespace Doinc\Wallet\Traits;

use Doinc\Wallet\Enums\TransactionType;
use Doinc\Wallet\Exceptions\CannotBuyProduct;
use Doinc\Wallet\Exceptions\CannotPay;
use Doinc\Wallet\Interfaces\Product;
use Doinc\Wallet\Models\Transaction;
use Doinc\Wallet\Observers\TransactionObserver;
use Doinc\Wallet\TransactionBuilder;
use Throwable;

trait CanPay
{
    use HasWallet;

    /**
     * Buy the provided product for free
     *
     * @param Product $product Product to buy
     * @return Transaction
     * @throws CannotBuyProduct
     * @throws Throwable
     */
    public function payFree(Product $product): Transaction
    {
        if(!$product->canBuy($this)) {
            throw new CannotBuyProduct();
        }

        $transaction = TransactionBuilder::init()
            ->from($this)
            ->to($product)
            ->withAmount("0")
            ->isConfirmed()
            ->withType(TransactionType::PAYMENT)
            ->syncWithProductMetadata()
            ->get();
        $transaction->saveOrFail();
        TransactionObserver::applyTransactionOnTheFly($transaction, $this);

        return $transaction;
    }

    /**
     * Buy the provided product without firing any exception, if exception occurs silence them and returns a null
     * object
     *
     * @param Product $product Product to buy
     * @return Transaction|null
     */
    public function safePay(Product $product): ?Transaction
    {
        try {
            return $this->pay($product);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Buy the provided product
     *
     * @param Product $product Product to buy
     * @param bool $confirmed Whether the transaction was confirmed or not, defaults to confirmed
     * @return Transaction
     * @throws CannotBuyProduct
     * @throws CannotPay
     * @throws Throwable
     */
    public function pay(Product $product, bool $confirmed = true): Transaction
    {
        if (!$product->canBuy($this)) {
            throw new CannotBuyProduct();
        }
        if(!$this->canWithdraw($product->getCostAttribute($this), true)) {
            throw new CannotPay();
        }

        $transaction = TransactionBuilder::init()
            ->from($this)
            ->to($product)
            ->withAmount("0")
            ->isConfirmed($confirmed)
            ->withType(TransactionType::PAYMENT)
            ->syncWithProductMetadata()
            ->get(compute_cost_from_product: true);
        $transaction->saveOrFail();
        TransactionObserver::applyTransactionOnTheFly($transaction, $this);

        return $transaction;
    }

    /**
     * Buy the provided product without caring if the balance is 0 or negative
     *
     * @param Product $product Product to buy
     * @return Transaction
     * @throws CannotBuyProduct
     * @throws CannotPay
     * @throws Throwable
     */
    public function forcePay(Product $product): Transaction
    {
        return $this->pay($product);
    }

    /**
     * Refund the provided product without firing any exception, if exception occurs silence them and returns false
     *
     * @param Product $product Product to refund
     * @return Transaction|null
     */
    public function safeRefund(Product $product): ?Transaction
    {
        try {
            return $this->refund($product);
        }
        catch (Throwable) {
            return null;
        }
    }

    /**
     * Refund the provided product
     *
     * @param Product $product Product to buy
     * @param bool $confirmed Whether the transaction was confirmed or not, defaults to confirmed
     * @return Transaction
     * @throws Throwable
     */
    public function refund(Product $product, bool $confirmed = true): Transaction
    {
        $transaction = TransactionBuilder::init()
            ->from($product)
            ->to($this)
            ->isConfirmed($confirmed)
            ->withType(TransactionType::REFUND)
            ->syncWithProductMetadata()
            ->get(compute_cost_from_product: true);
        $transaction->saveOrFail();
        TransactionObserver::applyTransactionOnTheFly($transaction, receiver: $this);

        return $transaction;
    }

    /**
     * Refund the provided product
     *
     * @param Product $product Product to buy
     * @return Transaction
     * @throws Throwable
     */
    public function forceRefund(Product $product): Transaction
    {
        return $this->refund($product);
    }

    /**
     * Get the payment for the provided product if it exists
     *
     * @param Product $product Product to look for
     * @return Transaction|null
     */
    public function getPayment(Product $product): ?Transaction
    {
        return $this->transactions()
            ->where("to_type", $product->getMorphClass())
            ->where("to_id", $product->getKey())
            ->where("status", TransactionType::PAYMENT)
            ->orderByDesc("id")
            ->first();
    }

    /**
     * Whether the provided product was bought
     *
     * @param Product $product Product to look for
     * @return bool
     */
    public function paid(Product $product): bool
    {
        return !is_null($this->getPayment($product));
    }
}
