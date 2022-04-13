<?php

namespace Doinc\Wallet\Tests\Samples;

use Doinc\Wallet\Interfaces\Customer;
use Doinc\Wallet\Interfaces\Discountable;
use Doinc\Wallet\Interfaces\MinimalTaxable;
use Doinc\Wallet\Interfaces\Product;
use Doinc\Wallet\Traits\HasDiscount;
use Doinc\Wallet\Traits\HasTax;
use Doinc\Wallet\Traits\HasWallet;
use Illuminate\Database\Eloquent\Model;

class SampleFullUnbuyableProduct extends Model implements Product, MinimalTaxable, Discountable
{
    use HasWallet, HasTax, HasDiscount;

    /**
     * Check whether the provided customer has enough funds to buy the given quantity of the current product
     *
     * @param Customer $customer Product buyer
     * @param int $quantity Amount of product buying
     * @param bool $force Whether the buyer's balance can go below 0
     * @return bool
     */
    public function canBuy(Customer $customer, int $quantity = 1, bool $force = false): bool
    {
        return false;
    }

    /**
     * Defines how much the product costs
     * This value by default is not stored in any field of the record
     *
     * @param Customer $customer Product buyer, useful to personalize the price per user
     * @return string
     */
    public function getCostAttribute(Customer $customer): string
    {
        return "1000";
    }

    /**
     * Metadata attributes assigned to the product, this can be used to identify one or more products while
     * examining transactions & transfers
     *
     * @return array
     */
    public function getMetadataAttribute(): array
    {
        return [];
    }

    /**
     * Defines the percentage of fee to be taken once a product gets paid
     *
     * @param Customer $customer Product buyer, useful to personalize the tax per user
     * @return int|string
     */
    public function getTaxPercentageAttribute(Customer $customer): int|string
    {
        return 50;
    }

    /**
     * Defines the minimum fee that will be applied to a product if the fee is lower than this value
     *
     * @return int|string
     */
    public function getMinimumTaxAttribute(): int|string
    {
        return 75;
    }

    /**
     * Defines the percentage of discount to be applied once a product gets paid
     *
     * @param Customer $customer Product buyer, useful to personalize the discount per user
     * @return int|string
     */
    public function getDiscountPercentageAttribute(Customer $customer): int|string
    {
        return 102;
    }

    /**
     * Defines the number of decimals used in discount representation
     *
     * @return int
     */
    public function getTaxPrecisionAttribute(): int {
        return 3;
    }

    /**
     * Defines the number of decimals used in discount representation
     *
     * @return int
     */
    public function getDiscountPrecisionAttribute(): int
    {
        return 3;
    }
}
