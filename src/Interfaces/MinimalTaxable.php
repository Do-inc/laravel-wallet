<?php

namespace Doinc\Wallet\Interfaces;

/**
 * @property int|string $minimum_tax
 */
interface MinimalTaxable extends Taxable
{
    /**
     * Defines the minimum fee that will be applied to a product if the fee is lower than this value
     *
     * @return int|string
     */
    public function getMinimumTaxAttribute(): int|string;
}
