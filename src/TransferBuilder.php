<?php

namespace Doinc\Wallet;

use Doinc\Wallet\Enums\TransferStatus;
use Doinc\Wallet\Exceptions\UnableToCreateTransaction;
use Doinc\Wallet\Interfaces\Discount;
use Doinc\Wallet\Interfaces\Product;
use Doinc\Wallet\Interfaces\Taxable;
use Doinc\Wallet\Interfaces\Wallet;
use Doinc\Wallet\Models\Transaction;
use Doinc\Wallet\Models\Transfer;
use JetBrains\PhpStorm\Pure;

class TransferBuilder
{
    /**
     * @var array<string, mixed>
     */
    protected array $attributes = [];

    protected Wallet $customer;
    protected Product $product;

    protected function __construct()
    {
        $this->attributes = [
            "from_type" => null,
            "from_id" => null,
            "to_type" => null,
            "to_id" => null,
            "status" => TransferStatus::TRANSFER,
            "status_last" => TransferStatus::TRANSFER,
            "fee" => "0",
            "discount" => "0",
            "deposit_id" => null,
            "withdraw_id" => null,
        ];
    }

    /**
     * Create a new instance of the transfer builder
     *
     * @return static
     */
    #[Pure]
    public static function init(): static
    {
        return new static;
    }

    /**
     * Attach the wallet from which the transfer starts
     *
     * @param Wallet $wallet
     * @return static
     */
    public function from(Wallet $wallet): static
    {
        $this->attributes["from_type"] = $wallet->getMorphClass();
        $this->attributes["from_id"] = $wallet->getKey();
        $this->customer = $wallet;

        return $this;
    }

    /**
     * Attach the wallet where the transfer arrives
     *
     * @param Wallet $wallet
     * @return static
     */
    public function to(Wallet $wallet): static
    {
        $this->attributes["to_type"] = $wallet->getMorphClass();
        $this->attributes["to_id"] = $wallet->getKey();

        return $this;
    }

    /**
     * Set the environment for the fee & discount computation
     *
     * @param Product $product
     * @return static
     */
    public function withProduct(Product $product): static {
        $this->product = $product;
        return $this;
    }

    /**
     * Create and store a transaction
     * NOTE: This method immediately stores the just created transaction
     *
     * @param Wallet $wallet
     * @param bool $is_deposit
     * @param string $amount
     * @param bool $is_confirmed
     * @param array|null $metadata
     * @return Transaction
     * @throws UnableToCreateTransaction
     */
    protected function buildTransaction(
        Wallet $wallet,
        bool   $is_deposit,
        string $amount = "0",
        bool   $is_confirmed = true,
        ?array $metadata = null
    ): Transaction
    {
        $transaction = TransactionBuilder::init()
            ->withWallet($wallet)
            ->withAmount($amount)
            ->withMetadata($metadata);

        if ($is_deposit) {
            $transaction->isDeposit();
        } else {
            $transaction->isWithdraw();
        }

        if ($is_confirmed) {
            $transaction->isConfirmed();
        } else {
            $transaction->isNotConfirmed();
        }

        $transaction = $transaction->get();
        if (!$transaction->save()) {
            throw new UnableToCreateTransaction();
        }

        return $transaction;
    }

    /**
     * Attach an already existing deposit transaction
     *
     * @param Transaction $transaction
     * @return static
     */
    public function attachDeposit(Transaction $transaction): static
    {
        $this->attributes["deposit_id"] = $transaction->getKey();

        return $this;
    }

    /**
     * Create and attach a deposit transaction
     *
     * @param Wallet $wallet
     * @param bool $is_deposit
     * @param string $amount
     * @param bool $is_confirmed
     * @param array|null $metadata
     * @return static
     * @throws UnableToCreateTransaction
     */
    public function createDeposit(
        Wallet $wallet,
        bool   $is_deposit,
        string $amount = "0",
        bool   $is_confirmed = true,
        ?array $metadata = null
    ): static
    {
        $this->computeDiscount($this->product);
        $this->computeFee($this->product);

        $discount = BigMath::div($this->attributes["discount"], BigMath::powTen($this->product->))
        $transaction = $this->buildTransaction($wallet, $is_deposit, $amount, $is_confirmed, $metadata);
        $this->attributes["deposit_id"] = $transaction->getKey();

        return $this;
    }

    /**
     * Attach an already existing withdraw transaction
     *
     * @param Transaction $transaction
     * @return static
     */
    public function attachWithdraw(Transaction $transaction): static
    {
        $this->attributes["withdraw_id"] = $transaction->getKey();

        return $this;
    }

    /**
     * Create and attach a withdraw transaction
     *
     * @param Wallet $wallet
     * @param bool $is_deposit
     * @param string $amount
     * @param bool $is_confirmed
     * @param array|null $metadata
     * @return static
     * @throws UnableToCreateTransaction
     */
    public function createWithdraw(
        Wallet $wallet,
        bool   $is_deposit,
        string $amount = "0",
        bool   $is_confirmed = true,
        ?array $metadata = null
    ): static
    {
        $this->computeDiscount($this->product);
        $this->computeFee($this->product);
        $transaction = $this->buildTransaction($wallet, $is_deposit, $amount, $is_confirmed, $metadata);
        $this->attributes["withdraw_id"] = $transaction->getKey();

        return $this;
    }

    /**
     * Define the transfer status
     *
     * @param TransferStatus $status
     * @return static
     */
    public function withStatus(TransferStatus $status): static {
        $this->attributes["status"] = $status;
        $this->attributes["status_last"] = $status;

        return $this;
    }

    /**
     * Computes the discount to apply to the transfer if needed
     *
     * @param Product $product
     * @return void
     */
    protected function computeDiscount(Product $product): void {
        if($this->attributes["discount"] === "0" && $product instanceof Discount) {
            $this->attributes["discount"] = $product->getDiscount($this->customer);
        }
    }

    protected function computeFee(Product $product): void {
        if($this->attributes["fee"] === "0" && $product instanceof Taxable) {
            $this->attributes["fee"] = $product->getFeePercent();
        }
    }

    /**
     * Get the configured transfer model based on the provided product instance
     *
     * @param Product $product
     * @return Transfer
     */
    public function get(Product $product): Transfer {
        $this->computeDiscount($product);
        $this->computeDiscount($product);

        return new Transfer($this->attributes);
    }
}
