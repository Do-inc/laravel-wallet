<?php

namespace Doinc\Wallet\Exceptions;

use Exception;

class CannotRefundUnpaidProduct extends Exception
{
    public function __construct()
    {
        parent::__construct(
            config("wallet.errors.CANNOT_REFUND_UNPAID_PRODUCT.message"),
            config("wallet.errors.CANNOT_REFUND_UNPAID_PRODUCT.code")
        );
    }
}
