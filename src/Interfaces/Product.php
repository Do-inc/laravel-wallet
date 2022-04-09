<?php

namespace Doinc\Wallet\Interfaces;

interface Product extends Wallet
{
    /**
     * Check whether the provided customer has enough funds to buy the given quantity of the current product
     *
     * @param Customer $customer Product buyer
     * @param int $quantity Amount of product buying
     * @param bool $force Whether the buyer's balance can go below 0
     * @return bool
     */
    public function canBuy(Customer $customer, int $quantity = 1, bool $force = false): bool;

    /**
     * Defines how much the product costs
     * This value by default is not stored in any field of the record
     *
     * @param Customer $customer Product buyer, useful to personalize the price per user
     * @return string
     */
    public function getCostAttribute(Customer $customer): string;

    /**
     * Metadata attributes assigned to the product, this can be used to identify one or more products while
     * examining transactions & transfers
     *
     * @return array
     */
    public function getMetadataAttribute(): array;
}
