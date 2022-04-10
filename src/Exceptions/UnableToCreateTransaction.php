<?php

namespace Doinc\Wallet\Exceptions;

use Exception;

class UnableToCreateTransaction extends Exception
{
    public function __construct()
    {
        parent::__construct(
            config("wallet.errors.UNABLE_TO_CREATE_TRANSACTION.message"),
            config("wallet.errors.UNABLE_TO_CREATE_TRANSACTION.code")
        );
    }
}
