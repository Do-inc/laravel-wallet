<?php

use Doinc\Wallet\Enums\TransactionType;
use Doinc\Wallet\Exceptions\InvalidWalletModelProvided;
use Doinc\Wallet\Exceptions\InvalidWalletOwner;
use Doinc\Wallet\Exceptions\TransactionAlreadyConfirmed;
use Doinc\Wallet\Models\Wallet;
use Doinc\Wallet\Tests\Samples\SampleNonConfirmableClass;

it('can confirm a pending transaction', function () {
    /** @var Wallet $sender */
    /** @var Wallet $receiver */
    [$sender, $receiver] = Wallet::factory(2)->create();
    $sender->deposit(10);
    $transaction = $sender->transfer($receiver, 1, confirmed: false);

    expect($transaction->from_id)->toBe($sender->id);
    expect($transaction->from_type)->toBe($sender::class);
    expect($transaction->to_id)->toBe($receiver->id);
    expect($transaction->to_type)->toBe($receiver::class);
    expect($transaction->type)->toBe(TransactionType::TRANSFER);
    expect($transaction->metadata->toArray())->toBe([]);
    expect($transaction->amount)->toBe("1.00");
    expect($transaction->discount)->toBe("0.00");
    expect($transaction->fee)->toBe("0.00");
    expect($transaction->confirmed)->toBeFalse();
    expect($transaction->confirmed_at)->toBeNull();
    expect($sender->balance)->toBe("10.00");
    expect($receiver->balance)->toBe("0.00");

    expect($sender->confirm($transaction))->toBeTrue();
    $transaction = $transaction->fresh();
    expect($transaction->confirmed)->toBeTrue();
    expect($transaction->confirmed_at)->not()->toBeNull();
    $sender = $sender->fresh();
    $receiver = $receiver->fresh();
    expect($sender->balance)->toBe("9.00");
    expect($receiver->balance)->toBe("1.00");
});

it('cannot confirm an already confirmed transaction', function () {
    /** @var Wallet $sender */
    /** @var Wallet $receiver */
    [$sender, $receiver] = Wallet::factory(2)->create();
    $sender->deposit(10);
    $transaction = $sender->transfer($receiver, 1, confirmed: false);

    expect($sender->confirm($transaction))->toBeTrue();
    $transaction = $transaction->fresh();
    expect($transaction->confirmed)->toBeTrue();
    expect($transaction->confirmed_at)->not()->toBeNull();

    $sender->confirm($transaction);
})->throws(TransactionAlreadyConfirmed::class);

it('can run safe confirm', function () {
    /** @var Wallet $sender */
    /** @var Wallet $receiver */
    [$sender, $receiver] = Wallet::factory(2)->create();
    $sender->deposit(10);
    $transaction = $sender->transfer($receiver, 1, confirmed: false);

    expect($transaction->from_id)->toBe($sender->id);
    expect($transaction->from_type)->toBe($sender::class);
    expect($transaction->to_id)->toBe($receiver->id);
    expect($transaction->to_type)->toBe($receiver::class);
    expect($transaction->type)->toBe(TransactionType::TRANSFER);
    expect($transaction->metadata->toArray())->toBe([]);
    expect($transaction->amount)->toBe("1.00");
    expect($transaction->discount)->toBe("0.00");
    expect($transaction->fee)->toBe("0.00");
    expect($transaction->confirmed)->toBeFalse();
    expect($transaction->confirmed_at)->toBeNull();
    expect($sender->balance)->toBe("10.00");
    expect($receiver->balance)->toBe("0.00");

    expect($sender->safeConfirm($transaction))->toBeTrue();
    $transaction = $transaction->fresh();
    expect($transaction->confirmed)->toBeTrue();
    expect($transaction->confirmed_at)->not()->toBeNull();
    $sender = $sender->fresh();
    $receiver = $receiver->fresh();
    expect($sender->balance)->toBe("9.00");
    expect($receiver->balance)->toBe("1.00");
});

it('can receive a null value if safe confirm fails', function () {
    /** @var Wallet $sender */
    /** @var Wallet $receiver */
    [$sender, $receiver] = Wallet::factory(2)->create();
    $sender->deposit(10);
    $transaction = $sender->transfer($receiver, 1, confirmed: false);

    expect($sender->safeConfirm($transaction))->toBeTrue();
    $transaction = $transaction->fresh();
    expect($transaction->confirmed)->toBeTrue();
    expect($transaction->confirmed_at)->not()->toBeNull();

    expect($sender->safeConfirm($transaction))->toBeFalse();
});

it('can reset transaction confirmation status', function () {
    /** @var Wallet $sender */
    /** @var Wallet $receiver */
    [$sender, $receiver] = Wallet::factory(2)->create();
    $sender->deposit(10);
    $transaction = $sender->transfer($receiver, 1, confirmed: false);

    expect($sender->confirm($transaction))->toBeTrue();
    $transaction = $transaction->fresh();
    expect($transaction->confirmed)->toBeTrue();
    expect($transaction->confirmed_at)->not()->toBeNull();
    expect($sender->resetConfirm($transaction))->toBeTrue();
    $transaction = $transaction->fresh();
    expect($transaction->confirmed)->toBeFalse();
    expect($transaction->confirmed_at)->toBeNull();
    expect($sender->resetConfirm($transaction))->toBeTrue();
    $transaction = $transaction->fresh();
    expect($transaction->confirmed)->toBeFalse();
    expect($transaction->confirmed_at)->toBeNull();
});

it('can run safe reset transaction confirmation status', function () {
    /** @var Wallet $sender */
    /** @var Wallet $receiver */
    [$sender, $receiver] = Wallet::factory(2)->create();
    $sender->deposit(10);
    $transaction = $sender->transfer($receiver, 1, confirmed: false);

    expect($sender->confirm($transaction))->toBeTrue();
    $transaction = $transaction->fresh();
    expect($transaction->confirmed)->toBeTrue();
    expect($transaction->confirmed_at)->not()->toBeNull();
    expect($sender->safeResetConfirm($transaction))->toBeTrue();
    $transaction = $transaction->fresh();
    expect($transaction->confirmed)->toBeFalse();
    expect($transaction->confirmed_at)->toBeNull();
    expect($sender->safeResetConfirm($transaction))->toBeTrue();
    $transaction = $transaction->fresh();
    expect($transaction->confirmed)->toBeFalse();
    expect($transaction->confirmed_at)->toBeNull();
});

it('malformed classes cannot call confirmation methods', function () {
    /** @var Wallet $sender */
    /** @var Wallet $receiver */
    [$sender, $receiver] = Wallet::factory(2)->create();
    $sender->deposit(10);
    $transaction = $sender->transfer($receiver, 1);

    $class = new SampleNonConfirmableClass();
    $class->resetConfirm($transaction);
})->throws(InvalidWalletModelProvided::class);

it('non owner cannot confirm transactions', function () {
    /** @var Wallet $sender */
    /** @var Wallet $receiver */
    /** @var Wallet $non_owner */
    [$sender, $receiver, $non_owner] = Wallet::factory(3)->create();
    $sender->deposit(10);
    $transaction = $sender->transfer($receiver, 1, confirmed: false);

    $non_owner->confirm($transaction);
})->throws(InvalidWalletOwner::class);

it('non owner cannot run safe reset on transactions', function () {
    /** @var Wallet $sender */
    /** @var Wallet $receiver */
    /** @var Wallet $non_owner */
    [$sender, $receiver, $non_owner] = Wallet::factory(3)->create();
    $sender->deposit(10);
    $transaction = $sender->transfer($receiver, 1);

    expect($non_owner->safeResetConfirm($transaction))->toBeFalse();
});
