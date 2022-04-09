<?php

namespace Doinc\Wallet\Exceptions;

use Exception;

class InvalidWalletModelProvided extends Exception
{
    public function __construct()
    {
        parent::__construct(
            config("wallet.errors.INVALID_WALLET_MODEL_PROVIDED.message"),
            config("wallet.errors.INVALID_WALLET_MODEL_PROVIDED.code")
        );
    }
}
