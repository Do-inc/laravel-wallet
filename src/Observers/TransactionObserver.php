<?php

namespace Doinc\Wallet\Observers;

use Doinc\Wallet\BigMath;
use Doinc\Wallet\Enums\TransactionType;
use Doinc\Wallet\Models\Transaction;
use Doinc\Wallet\Models\Wallet;
use Doinc\Wallet\Models\Wallet as WalletModel;

class TransactionObserver
{
    /**
     * Handle the Transaction "created" event.
     *
     * @param Transaction $transaction
     * @return void
     * @throws \Throwable
     */
    public function saved(Transaction $transaction): void
    {
        if ($transaction->confirmed) {
            /** @var Wallet $from */
            $from = $transaction->from;
            /** @var Wallet $to */
            $to = $transaction->to;

            $due = BigMath::sub(BigMath::add($transaction->amount, $transaction->fee), $transaction->discount);
            switch ($transaction->type) {
                case TransactionType::DEPOSIT:
                case TransactionType::REFUND:
                    if (self::isWallet($to)) {
                        $to->balance = BigMath::add($to->balance, $due);
                    } else {
                        $to->wallet->balance = BigMath::add($to->wallet->balance, $due);
                    }

                    break;
                case TransactionType::WITHDRAW:
                case TransactionType::PAYMENT:
                    if (self::isWallet($from)) {
                        $from->balance = BigMath::sub($from->balance, $due);
                    } else {
                        $from->wallet->balance = BigMath::sub($from->wallet->balance, $due);
                    }

                    break;
                case TransactionType::TRANSFER:
                    if (self::isWallet($to)) {
                        $from->balance = BigMath::sub($from->balance, $due);
                    } else {
                        $from->wallet->balance = BigMath::sub($from->wallet->balance, $due);
                    }

                    if (self::isWallet($to)) {
                        $to->balance = BigMath::add($to->balance, $due);
                    } else {
                        $to->wallet->balance = BigMath::add($to->wallet->balance, $due);
                    }

                    break;
            }

            // save modification
            if (! is_null($from)) {
                $from->saveOrFail();
            }
            if (! is_null($to)) {
                $to->saveOrFail();
            }
        }
    }

    /**
     * Apply the transaction modification to the provided instance on the fly.
     * This method avoids the need to refresh a wallet instance as soon as a transaction is executed
     *
     * @param Transaction $transaction
     * @param object|null $sender
     * @param object|null $receiver
     * @return void
     */
    public static function applyTransactionOnTheFly(Transaction $transaction, ?object &$sender = null, ?object &$receiver = null): void
    {
        if ($transaction->confirmed) {
            $due = BigMath::sub(BigMath::add($transaction->amount, $transaction->fee), $transaction->discount);
            switch ($transaction->type) {
                case TransactionType::DEPOSIT:
                    if (self::isWallet($receiver)) {
                        $receiver->balance = BigMath::add($receiver->balance, $due);
                    } else {
                        $receiver->wallet->balance = BigMath::add($receiver->wallet->balance, $due);
                    }

                    break;
                case TransactionType::REFUND:
                    if (! $transaction->refunded) {
                        // the amount to refund is always only the paid amount without the fee
                        $due = BigMath::sub($transaction->amount, $transaction->discount);
                        if (self::isWallet($receiver)) {
                            $receiver->balance = BigMath::add($receiver->balance, $due);
                        } else {
                            $receiver->wallet->balance = BigMath::add($receiver->wallet->balance, $due);
                        }
                    }

                    break;
                case TransactionType::WITHDRAW:
                case TransactionType::PAYMENT:
                    if (self::isWallet($sender)) {
                        $sender->balance = BigMath::sub($sender->balance, $due);
                    } else {
                        $sender->wallet->balance = BigMath::sub($sender->wallet->balance, $due);
                    }

                    break;
                case TransactionType::TRANSFER:
                    if (! is_null($sender) && $transaction->from_id === $sender->id) {
                        if (self::isWallet($sender)) {
                            $sender->balance = BigMath::sub($sender->balance, $due);
                        } else {
                            $sender->wallet->balance = BigMath::sub($sender->wallet->balance, $due);
                        }
                    }

                    if (! is_null($receiver) && $transaction->to_id === $receiver->id) {
                        if (self::isWallet($sender)) {
                            $receiver->balance = BigMath::add($receiver->balance, $due);
                        } else {
                            $receiver->wallet->balance = BigMath::add($receiver->wallet->balance, $due);
                        }
                    }

                    break;
            }
        }
    }

    protected static function isWallet(?object $wallet): bool
    {
        return !is_null($wallet) && $wallet instanceof WalletModel;
    }
}
