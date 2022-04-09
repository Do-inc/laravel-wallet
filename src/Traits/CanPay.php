<?php

namespace Doinc\Wallet\Traits;

use Doinc\Wallet\Enums\TransferStatus;
use Doinc\Wallet\Exceptions\CannotBuyProduct;
use Doinc\Wallet\Interfaces\Product;
use Doinc\Wallet\Models\Transfer;

trait CanPay
{
    use HasWallet;

    /**
     * Buy the provided product for free
     *
     * @param Product $product Product to buy
     * @return Transfer
     */
    public function payFree(Product $product): Transfer
    {
        if (! $product->canBuy($this)) {
            throw new CannotBuyProduct();
        }

        return current($this->payFreeCart(app(Cart::class)->withItem($product)));
    }

    /**
     * Buy the provided product without firing any exception, if exception occurs silence them and returns a null
     * object
     *
     * @param Product $product Product to buy
     * @return Transfer|null
     */
    public function safePay(Product $product): ?Transfer
    {
        return current($this->safePayCart(app(Cart::class)->withItem($product), $force)) ?: null;
    }

    /**
     * Buy the provided product
     *
     * @param Product $product Product to buy
     * @return Transfer
     */
    public function pay(Product $product): Transfer
    {
        return current($this->payCart(app(Cart::class)->withItem($product), $force));
    }

    /**
     * Buy the provided product without caring if the balance is 0 or negative
     *
     * @param Product $product Product to buy
     * @return Transfer
     */
    public function forcePay(Product $product): Transfer
    {
        return current($this->forcePayCart(app(Cart::class)->withItem($product)));
    }

    /**
     * Refund the provided product without firing any exception, if exception occurs silence them and returns false
     *
     * @param Product $product Product to refund
     * @return bool
     */
    public function safeRefund(Product $product): bool
    {
        return $this->safeRefundCart(app(Cart::class)->withItem($product), $force, $gifts);
    }

    /**
     * Refund the provided product
     *
     * @param Product $product Product to buy
     * @return bool
     */
    public function refund(Product $product): bool
    {
        return $this->refundCart(app(Cart::class)->withItem($product), $force, $gifts);
    }

    /**
     * Get the payment for the provided product if it exists
     *
     * @param Product $product Product to look for
     * @return Transfer|null
     */
    public function getPayment(Product $product): ?Transfer
    {
        return $this->transfers()
            ->where("to_type", $product->getMorphClass())
            ->where("to_id", $product->getKey())
            ->where("status", TransferStatus::PAID)
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
        return ! is_null($this->getPayment($product));
    }
}
