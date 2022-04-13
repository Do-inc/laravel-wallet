<?php

namespace Doinc\Wallet\Exceptions;

use Exception;

class CannotPay extends Exception
{
    public function __construct()
    {
        parent::__construct(
            config("wallet.errors.CANNOT_PAY.message"),
            config("wallet.errors.CANNOT_PAY.code")
        );
    }
}
