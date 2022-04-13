<?php

namespace Doinc\Wallet\Traits;

/**
 * @property int $tax_precision
 */
trait HasTax
{
    /**
     * Defines the number of decimals used in discount representation
     *
     * @return int
     */
    public function getTaxPrecisionAttribute(): int
    {
        return config("wallet.precision.tax");
    }
}
