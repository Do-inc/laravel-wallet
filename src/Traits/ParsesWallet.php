<?php

namespace Doinc\Wallet\Traits;

use Doinc\Wallet\Exceptions\InvalidWalletModelProvided;
use Doinc\Wallet\Interfaces\Wallet;
use Doinc\Wallet\Models\Wallet as WalletModel;
use Throwable;

trait ParsesWallet
{
    /**
     * Get the wallet instance from a provided object
     *
     * @param object $wallet
     * @return WalletModel
     * @throws InvalidWalletModelProvided
     */
    protected function getWallet(object $wallet): WalletModel
    {
        if ($wallet instanceof WalletModel) {
            return $wallet;
        }
        try {
            return $wallet->wallet;
        } catch (Throwable) {
            throw new InvalidWalletModelProvided();
        }
    }

    protected function isWallet(Wallet $wallet): bool
    {
        return $wallet instanceof WalletModel;
    }
}
