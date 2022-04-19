<?php

namespace Doinc\Wallet\Traits;

use Doinc\Wallet\BigMath;
use Doinc\Wallet\Enums\TransactionType;
use Doinc\Wallet\Exceptions\CannotTransfer;
use Doinc\Wallet\Exceptions\CannotWithdraw;
use Doinc\Wallet\Exceptions\InvalidWalletModelProvided;
use Doinc\Wallet\Interfaces\Wallet;
use Doinc\Wallet\Models\Transaction;
use Doinc\Wallet\Models\Wallet as WalletModel;
use Doinc\Wallet\Observers\TransactionObserver;
use Doinc\Wallet\TransactionBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Throwable;

trait HasWallet
{
    /**
     * Top up the wallet with the provided amount
     *
     * @param int|float|string $amount Funds to add to the wallet
     * @param array $metadata Optional metadata to add to the transaction
     * @param bool $confirmed Whether the transaction was confirmed or not, defaults to confirmed
     * @return Transaction
     * @throws Throwable
     */
    public function deposit(int|float|string $amount, array $metadata = [], bool $confirmed = true): Transaction
    {
        $transaction = TransactionBuilder::init()
            ->to($this->getWallet($this))
            ->withAmount($amount)
            ->withMetadata($metadata)
            ->withType(TransactionType::DEPOSIT)
            ->isConfirmed($confirmed)
            ->get();
        $transaction->saveOrFail();

        if ($this->isWallet($this)) {
            TransactionObserver::applyTransactionOnTheFly($transaction, receiver: $this);
        } else {
            TransactionObserver::applyTransactionOnTheFly($transaction, receiver: $this->wallet);
        }

        return $transaction;
    }

    /**
     * Withdraw the provided amount of funds from the wallet
     *
     * @param float|int|string $amount Funds to withdraw from the wallet
     * @param array $metadata Optional metadata to add to the transaction
     * @param bool $confirmed Whether the transaction was confirmed or not, defaults to confirmed
     * @return Transaction
     * @throws CannotTransfer
     * @throws Throwable
     */
    public function withdraw(float|int|string $amount, array $metadata = [], bool $confirmed = true): Transaction
    {
        if (! $this->canWithdraw($amount, true)) {
            throw new CannotWithdraw();
        }

        $transaction = TransactionBuilder::init()
            ->from($this->getWallet($this))
            ->withAmount($amount)
            ->withMetadata($metadata)
            ->withType(TransactionType::WITHDRAW)
            ->isConfirmed($confirmed)
            ->get();
        $transaction->saveOrFail();

        if ($this->isWallet($this)) {
            TransactionObserver::applyTransactionOnTheFly($transaction, $this);
        } else {
            TransactionObserver::applyTransactionOnTheFly($transaction, $this->wallet);
        }

        return $transaction;
    }

    /**
     * Forcefully withdraw funds from the wallet without caring if the balance is 0 or negative
     *
     * @param float|int|string $amount Funds to withdraw from the wallet
     * @param array $metadata Optional metadata to add to the transaction
     * @return Transaction
     * @throws Throwable
     */
    public function forceWithdraw(float|int|string $amount, array $metadata = []): Transaction
    {
        return $this->withdraw($amount, $metadata);
    }

    /**
     * Transfer the provided amount of funds from the wallet to the recipient address
     *
     * @param Wallet $recipient Wallet where the funds will be transferred
     * @param int|float|string $amount Funds to transfer from the wallet
     * @param array $metadata Optional metadata to add to the transaction
     * @param bool $confirmed Whether the transaction was confirmed or not, defaults to confirmed
     * @return Transaction
     * @throws CannotTransfer
     * @throws InvalidWalletModelProvided
     * @throws Throwable
     */
    public function transfer(
        Wallet           $recipient,
        int|float|string $amount,
        array            $metadata = [],
        bool             $confirmed = true
    ): Transaction {
        if (! $this->canWithdraw($amount, true)) {
            throw new CannotTransfer();
        }
        if (! $recipient instanceof WalletModel) {
            throw new InvalidWalletModelProvided();
        }

        $transaction = TransactionBuilder::init()
            ->from($this->getWallet($this))
            ->to($this->getWallet($recipient))
            ->withAmount($amount)
            ->withMetadata($metadata)
            ->withType(TransactionType::TRANSFER)
            ->isConfirmed($confirmed)
            ->get();
        $transaction->saveOrFail();

        $sender_is_wallet = $this->isWallet($this);
        $receiver_is_wallet = $this->isWallet($this);
        if ($sender_is_wallet && $receiver_is_wallet) {
            TransactionObserver::applyTransactionOnTheFly($transaction, $this, $recipient);
        } elseif (! $sender_is_wallet && $receiver_is_wallet) {
            TransactionObserver::applyTransactionOnTheFly($transaction, $this->wallet, $recipient);
        } elseif ($sender_is_wallet && ! $receiver_is_wallet) {
            TransactionObserver::applyTransactionOnTheFly($transaction, $this, $recipient->wallet);
        } else {
            TransactionObserver::applyTransactionOnTheFly($transaction, $this->wallet, $recipient->wallet);
        }

        return $transaction;
    }

    /**
     * Transfer the provided amount of funds from the wallet to the recipient address without firing any exception,
     * if exception occurs silence them and returns a null object
     *
     * @param Wallet $recipient Wallet where the funds will be transferred
     * @param int|float|string $amount Funds to transfer from the wallet
     * @param array $metadata Optional metadata to add to the transaction
     * @param bool $confirmed Whether the transaction was confirmed or not, defaults to confirmed
     * @return Transaction|null
     */
    public function safeTransfer(
        Wallet           $recipient,
        int|float|string $amount,
        array            $metadata = [],
        bool             $confirmed = true
    ): ?Transaction {
        try {
            return $this->transfer($recipient, $amount, $metadata, $confirmed);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Forcefully transfer the provided amount of funds from the wallet to the recipient address without caring if the
     * balance is 0 or negative
     *
     * @param Wallet $recipient Wallet where the funds will be transferred
     * @param int|float|string $amount Funds to transfer from the wallet
     * @param array $metadata Optional metadata to add to the transaction
     * @return Transaction
     * @throws Throwable
     */
    public function forceTransfer(Wallet $recipient, int|float|string $amount, array $metadata = []): Transaction
    {
        return $this->transfer($recipient, $amount, $metadata);
    }

    /**
     * Check whether the balance is enough to withdraw the provided amount
     *
     * @param int|float|string $amount
     * @param bool $allow_zero
     * @return bool
     * @throws InvalidWalletModelProvided
     */
    public function canWithdraw(int|float|string $amount, bool $allow_zero = false): bool
    {
        $wallet = $this->getWallet($this);

        return BigMath::higherThan($wallet->balance, $amount) || (
            $allow_zero && BigMath::equal($wallet->balance, $amount)
        );
    }

    /**
     * Retrieve all the wallet transaction
     *
     * @return Builder
     */
    public function transactions(): Builder
    {
        $wallet = $this->getWallet($this);

        return Transaction::query()
            ->where(function (Builder $builder) use ($wallet) {
                $builder->where("from_type", WalletModel::class)
                    ->where("from_id", $wallet->getKey());
            })
            ->orWhere(function (Builder $builder) use ($wallet) {
                $builder->where("to_type", WalletModel::class)
                    ->where("to_id", $wallet->getKey());
            });
    }

    /**
     * Retrieve all the sent transaction
     *
     * @return MorphMany
     */
    public function sentTransactions(): MorphMany
    {
        return $this->getWallet($this)->morphMany(Transaction::class, "from");
    }

    /**
     * Retrieve all the received transaction
     *
     * @return MorphMany
     */
    public function receivedTransactions(): MorphMany
    {
        return $this->getWallet($this)->morphMany(Transaction::class, "to");
    }

    /**
     * Get the associated wallet
     *
     * @return WalletModel|HasOne
     */
    public function wallet(): WalletModel|HasOne
    {
        return $this->isWallet($this) ? $this : $this->hasOne(WalletModel::class);
    }
}
