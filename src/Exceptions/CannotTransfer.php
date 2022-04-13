<?php

namespace Doinc\Wallet\Exceptions;

use Exception;

class CannotTransfer extends Exception
{
    public function __construct()
    {
        parent::__construct(
            config("wallet.errors.CANNOT_TRANSFER.message"),
            config("wallet.errors.CANNOT_TRANSFER.code")
        );
    }
}
