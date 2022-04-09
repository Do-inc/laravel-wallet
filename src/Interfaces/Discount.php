<?php

namespace Doinc\Wallet\Interfaces;

interface Discount
{
    /**
     * Defines the percentage of discount to be applied once a product gets paid
     *
     * @param Customer $customer Product buyer, useful to personalize the discount per user
     * @return int|float|string
     */
    public function getPersonalDiscount(Customer $customer): int|float|string;
}
