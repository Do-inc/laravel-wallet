<?php

namespace Doinc\Wallet\Exceptions;

use Exception;

class TransactionAlreadyConfirmed extends Exception
{
    public function __construct()
    {
        parent::__construct(
            config("wallet.errors.TRANSACTION_ALREADY_CONFIRMED.message"),
            config("wallet.errors.TRANSACTION_ALREADY_CONFIRMED.code")
        );
    }
}
