<?php

namespace Doinc\Wallet\Tests\Samples;

use Doinc\Wallet\Interfaces\Customer;
use Doinc\Wallet\Interfaces\Discountable;
use Doinc\Wallet\Interfaces\Product;
use Doinc\Wallet\Traits\HasDiscount;
use Doinc\Wallet\Traits\HasWallet;
use Illuminate\Database\Eloquent\Model;

class SampleDiscountableProduct extends Model implements Product, Discountable
{
    use HasWallet, HasDiscount;

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
        return true;
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
     * Defines the percentage of discount to be applied once a product gets paid
     *
     * @param Customer $customer Product buyer, useful to personalize the discount per user
     * @return int|string
     */
    public function getDiscountPercentageAttribute(Customer $customer): int|string
    {
        return 10;
    }
}
