<?php

namespace Doinc\Wallet;

use Doinc\Wallet\Enums\TransactionType;
use Doinc\Wallet\Interfaces\Customer;
use Doinc\Wallet\Interfaces\Discountable;
use Doinc\Wallet\Interfaces\MinimalTaxable;
use Doinc\Wallet\Interfaces\Product;
use Doinc\Wallet\Interfaces\Taxable;
use Doinc\Wallet\Interfaces\Wallet;
use Doinc\Wallet\Models\Transaction;
use Illuminate\Support\Collection;

class TransactionBuilder
{
    /**
     * List of attributes that can be edited in the transaction object
     *
     * @var array<string, mixed>
     */
    protected array $attributes;

    protected ?Product $product = null;
    protected ?Customer $customer = null;

    protected function __construct()
    {
        $this->attributes = [
            "from_id" => null,
            "from_type" => null,
            "to_id" => null,
            "to_type" => null,
            "type" => null,
            "amount" => "0",
            "confirmed" => true,
            "confirmed_at" => now(),
            "metadata" => [],
            "discount" => "0",
            "fee" => "0",
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
     * Define which wallet the transaction will start from
     *
     * @param Wallet $wallet
     * @return static
     */
    public function from(Wallet $wallet): static
    {
        $this->attributes["from_id"] = $wallet->getKey();
        $this->attributes["from_type"] = $wallet->getMorphClass();

        if ($wallet instanceof Product && is_null($this->product)) {
            $this->product = $wallet;
        } elseif ($wallet instanceof Customer && is_null($this->customer)) {
            $this->customer = $wallet;
        }

        return $this;
    }

    /**
     * Define which wallet the transaction will arrive to
     *
     * @param Product $wallet
     * @return static
     */
    public function to(Wallet $wallet): static
    {
        $this->attributes["to_id"] = $wallet->getKey();
        $this->attributes["to_type"] = $wallet->getMorphClass();

        if ($wallet instanceof Product && is_null($this->product)) {
            $this->product = $wallet;
        } elseif ($wallet instanceof Customer && is_null($this->customer)) {
            $this->customer = $wallet;
        }

        return $this;
    }

    /**
     * Define the transaction type
     *
     * @param TransactionType $type
     * @return static
     */
    public function withType(TransactionType $type): static
    {
        $this->attributes["type"] = $type;

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
     * Define the confirmation status of the transaction
     *
     * @param bool $confirmed
     * @return static
     */
    public function isConfirmed(bool $confirmed = true): static
    {
        if ($confirmed) {
            $this->attributes["confirmed"] = true;
            $this->attributes["confirmed_at"] = now();
        } else {
            $this->attributes["confirmed"] = false;
            $this->attributes["confirmed_at"] = null;
        }

        return $this;
    }

    /**
     * Attach the provided metadata to the transaction
     *
     * @param array|Collection $metadata
     * @return static
     */
    public function withMetadata(array|Collection $metadata): static
    {
        $this->attributes["metadata"] = $metadata;

        return $this;
    }

    /**
     * Define the discount applied to the amount.
     * This value must be stored in amount's base points
     *
     * @param string $discount
     * @return static
     */
    public function withDiscount(string $discount): static
    {
        $this->attributes["discount"] = $discount;

        return $this;
    }

    /**
     * Define the fee applied to the amount.
     * This value must be stored in amount's base points
     *
     * @param string $tax
     * @return static
     */
    public function withTax(string $tax): static
    {
        $this->attributes["fee"] = $tax;

        return $this;
    }

    /**
     * Synchronize the transaction metadata with the one of the product
     *
     * @return static
     */
    public function syncWithProductMetadata(): static
    {
        if (empty($this->attributes["metadata"])) {
            $this->attributes["metadata"] = $this->product->getMetadataAttribute();
        }

        return $this;
    }

    /**
     * Tries to compute the discount if possible
     *
     * @return void
     */
    protected function computeDiscount(): void
    {
        $amount = $this->attributes["amount"];

        // empty amount or unset product will make discount fallback to 0
        if ($amount === "0") {
            $this->withDiscount("0");
        } // if the discount was not inserted than can be automatically computed using the product instance
        elseif (
            $this->attributes["discount"] === "0" &&
            $this->product instanceof Discountable
        ) {
            // multiply raw amount to the discount percentage (as big number), than scale it back to a balance
            // compatible value dividing it by the discount precision.
            // ends truncating eventual decimals
            $this->attributes["discount"] = BigMath::div(
                BigMath::mul($amount, $this->product->getDiscountPercentageAttribute($this->customer)),
                BigMath::powTen($this->product->getDiscountPrecisionAttribute())
            );
        }
    }

    /**
     * Tries to compute the tax if possible
     *
     * @return void
     */
    protected function computeTax(): void
    {
        $amount = $this->attributes["amount"];

        // empty amount or unset product will make tax fallback to 0
        if ($amount === "0") {
            $this->withTax("0");
            return;
        } // if the tax was not inserted than can be automatically computed using the product instance
        elseif (
            $this->attributes["fee"] === "0" &&
            $this->product instanceof Taxable
        ) {
            // multiply raw amount to the tax percentage (as big number), than scale it back to a balance
            // compatible value dividing it by the tax precision.
            // ends truncating eventual decimals
            $this->attributes["fee"] = BigMath::div(
                BigMath::mul($amount, $this->product->getTaxPercentageAttribute($this->customer)),
                BigMath::powTen($this->product->getTaxPrecisionAttribute())
            );
        }

        // if the tax was lower than the minimum allowed recompute the tax using the minimum value
        if (
            $this->product instanceof MinimalTaxable &&
            BigMath::lowerThan(
                $this->product->getTaxPercentageAttribute($this->customer),
                $this->product->getMinimumTaxAttribute()
            )
        ) {
            $this->attributes["fee"] = BigMath::div(
                BigMath::mul($amount, $this->product->getMinimumTaxAttribute()),
                BigMath::powTen($this->product->getTaxPrecisionAttribute())
            );
        }
    }

    protected function computeCostFromProduct(): void
    {
        if ($this->attributes["amount"] === "0") {
            $this->attributes["amount"] = $this->product->getCostAttribute($this->customer);
        }
    }

    /**
     * Get the configured transaction model
     *
     * @param bool $compute_cost_from_product
     * @return Transaction
     */
    public function get(bool $compute_cost_from_product = false): Transaction
    {
        if ($compute_cost_from_product) {
            $this->computeCostFromProduct();
        }

        $this->computeDiscount();
        $this->computeTax();

        return new Transaction($this->attributes);
    }
}
