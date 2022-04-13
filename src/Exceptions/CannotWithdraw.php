<?php

namespace Doinc\Wallet\Exceptions;

use Exception;

class CannotWithdraw extends Exception
{
    public function __construct()
    {
        parent::__construct(
            config("wallet.errors.CANNOT_WITHDRAW.message"),
            config("wallet.errors.CANNOT_WITHDRAW.code")
        );
    }
}
