<?php

namespace Doinc\Wallet\Interfaces;

/**
 * @property int|string $discount_percentage
 * @property int $discount_precision
 */
interface Discountable
{
    /**
     * Defines the percentage of discount to be applied once a product gets paid
     *
     * @param Customer $customer Product buyer, useful to personalize the discount per user
     * @return int|string
     */
    public function getDiscountPercentageAttribute(Customer $customer): int|string;

    /**
     * Defines the number of decimals used in discount representation
     *
     * @return int
     */
    public function getDiscountPrecisionAttribute(): int;
}
