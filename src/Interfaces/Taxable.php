<?php

namespace Doinc\Wallet\Interfaces;

/**
 * @property int|string $tax_percentage
 * @property int $tax_precision
 */
interface Taxable
{
    /**
     * Defines the percentage of fee to be taken once a product gets paid
     *
     * @param Customer $customer Product buyer, useful to personalize the tax per user
     * @return int|string
     */
    public function getTaxPercentageAttribute(Customer $customer): int|string;

    /**
     * Defines the number of decimals used in fee representation
     *
     * @return int
     */
    public function getTaxPrecisionAttribute(): int;
}
