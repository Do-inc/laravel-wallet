<?php

namespace Doinc\Wallet\Interfaces;

interface Taxable
{
    /**
     * Defines the percentage of fee to be taken once a product gets paid
     *
     * @return int|float|string
     */
    public function getFeePercent(): int|float|string;

    /**
     * Defines the number of decimals used in fee representation
     *
     * @return int
     */
    public function getFeePrecisionAttribute(): int;
}
