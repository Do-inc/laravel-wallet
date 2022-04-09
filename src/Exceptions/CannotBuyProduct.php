<?php

namespace Doinc\Wallet\Exceptions;

use Exception;

class CannotBuyProduct extends Exception
{
    public function __construct()
    {
        parent::__construct(
            config("wallet.errors.CANNOT_BUY_PRODUCT.message"),
            config("wallet.errors.CANNOT_BUY_PRODUCT.code")
        );
    }
}
