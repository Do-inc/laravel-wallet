<?php

namespace Doinc\Wallet\Traits;

use Doinc\Wallet\Exceptions\InvalidWalletModelProvided;
use Doinc\Wallet\Models\Wallet as WalletModel;
use Exception;
use Throwable;

trait ParsesWallet
{
    /**
     * Get the wallet instance from a provided object
     *
     * @param object|null $wallet
     * @return WalletModel
     * @throws InvalidWalletModelProvided
     */
    protected function getWallet(?object $wallet): WalletModel
    {
        if ($this->isWallet($wallet)) {
            return $wallet;
        }

        try {
            if($this->isWallet($wallet->wallet)) {
                return $wallet->wallet;
            }
            throw new Exception();
        } catch (Throwable) {
            throw new InvalidWalletModelProvided();
        }
    }

    protected function isWallet(?object $wallet): bool
    {
        return !is_null($wallet) && $wallet instanceof WalletModel;
    }
}
