<?php

namespace Doinc\Wallet\Observers;

use Doinc\Wallet\BigMath;
use Doinc\Wallet\Enums\TransactionType;
use Doinc\Wallet\Models\Transaction;
use Doinc\Wallet\Models\Wallet;

class TransactionObserver
{
    /**
     * Handle the Transaction "created" event.
     *
     * @param Transaction $transaction
     * @return void
     */
    public function saved(Transaction $transaction)
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
                    $to->balance = BigMath::add($to->balance, $due);
                    break;
                case TransactionType::WITHDRAW:
                case TransactionType::PAYMENT:
                    $from->balance = BigMath::sub($from->balance, $due);
                    break;
                case TransactionType::TRANSFER:
                    $from->balance = BigMath::sub($from->balance, $due);
                    $to->balance = BigMath::add($to->balance, $due);
                    break;
            }

            // save modification
            if (!is_null($from)) {
                $from->save();
            }
            if (!is_null($to)) {
                $to->save();
            }
        }
    }

    /**
     * Apply the transaction modification to the provided instance on the fly.
     * This method avoids the need to refresh a wallet instance as soon as a transaction is executed
     *
     * @param Transaction $transaction
     * @param Wallet|null $sender
     * @param Wallet|null $receiver
     * @return void
     */
    public static function applyTransactionOnTheFly(Transaction $transaction, ?Wallet &$sender = null, ?Wallet &$receiver = null)
    {
        if ($transaction->confirmed) {
            $due = BigMath::sub(BigMath::add($transaction->amount, $transaction->fee), $transaction->discount);
            switch ($transaction->type) {
                case TransactionType::DEPOSIT:
                    $receiver->balance = BigMath::add($receiver->balance, $due);
                    break;
                case TransactionType::REFUND:
                    if (!$transaction->refunded) {
                        // the amount to refund is always only the paid amount without the fee
                        $due = BigMath::sub($transaction->amount, $transaction->discount);
                        $receiver->balance = BigMath::add($receiver->balance, $due);
                    }
                    break;
                case TransactionType::WITHDRAW:
                case TransactionType::PAYMENT:
                    $sender->balance = BigMath::sub($sender->balance, $due);
                    break;
                case TransactionType::TRANSFER:
                    if (!is_null($sender) && $transaction->from_id === $sender->id) {
                        $sender->balance = BigMath::sub($sender->balance, $due);
                    }

                    if (!is_null($receiver) && $transaction->to_id === $receiver->id) {
                        $receiver->balance = BigMath::add($receiver->balance, $due);
                    }
                    break;
            }
        }
    }
}
