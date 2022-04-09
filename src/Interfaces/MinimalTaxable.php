<?php

namespace Doinc\Wallet\Interfaces;

interface MinimalTaxable extends Taxable
{
    /**
     * Defines the minimum fee that will be applied to a product if the fee is lower than this value
     *
     * @return int|float|string
     */
    public function getMinimumFeeAttribute(): int|float|string;
}
