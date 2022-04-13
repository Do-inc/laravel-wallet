<?php

namespace Doinc\Wallet\Traits;

/**
 * @property int $discount_precision
 */
trait HasDiscount
{
    /**
     * Defines the number of decimals used in discount representation
     *
     * @return int
     */
    public function getDiscountPrecisionAttribute(): int
    {
        return config("wallet.precision.discount");
    }
}
