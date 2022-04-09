<?php

namespace Doinc\Wallet\Exceptions;

use Exception;

class InvalidWalletOwner extends Exception
{
    public function __construct()
    {
        parent::__construct(
            config("wallet.errors.INVALID_WALLET_OWNER.message"),
            config("wallet.errors.INVALID_WALLET_OWNER.code")
        );
    }
}
