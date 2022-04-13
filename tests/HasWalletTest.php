<?php

use Doinc\Wallet\Enums\TransactionType;
use Doinc\Wallet\Exceptions\CannotTransfer;
use Doinc\Wallet\Exceptions\CannotWithdraw;
use Doinc\Wallet\Exceptions\InvalidWalletModelProvided;
use Doinc\Wallet\Models\Wallet;
use Doinc\Wallet\Tests\Samples\SampleFullProduct;

it('can deposit', function () {
    /** @var Wallet $wallet */
    $wallet = Wallet::factory()->create();
    $transaction = $wallet->deposit(10, ["test" => true]);

    expect($transaction->to_id)->toBe($wallet->id);
    expect($transaction->to_type)->toBe($wallet::class);
    expect($transaction->from_id)->toBeNull();
    expect($transaction->from_type)->toBeNull();
    expect($transaction->type)->toBe(TransactionType::DEPOSIT);
    expect($transaction->metadata->toArray())->toBe([
        "test" => true,
    ]);
    expect($transaction->amount)->toBe("10.00");
    expect($transaction->discount)->toBe("0.00");
    expect($transaction->fee)->toBe("0.00");
    expect($wallet->balance)->toBe("10.00");
});

it('can withdraw', function () {
    /** @var Wallet $wallet */
    $wallet = Wallet::factory()->create();
    $wallet->deposit(10, ["test" => false]);
    $transaction = $wallet->withdraw(5, ["test" => true]);

    expect($transaction->from_id)->toBe($wallet->id);
    expect($transaction->from_type)->toBe($wallet::class);
    expect($transaction->to_id)->toBeNull();
    expect($transaction->to_type)->toBeNull();
    expect($transaction->type)->toBe(TransactionType::WITHDRAW);
    expect($transaction->metadata->toArray())->toBe([
        "test" => true,
    ]);
    expect($transaction->amount)->toBe("5.00");
    expect($transaction->discount)->toBe("0.00");
    expect($transaction->fee)->toBe("0.00");
    expect($wallet->balance)->toBe("5.00");
});

it('cannot withdraw if not confirmed', function () {
    /** @var Wallet $wallet */
    $wallet = Wallet::factory()->create();
    $wallet->deposit(10, ["test" => false]);
    $transaction = $wallet->withdraw(5, ["test" => true], false);

    expect($transaction->from_id)->toBe($wallet->id);
    expect($transaction->from_type)->toBe($wallet::class);
    expect($transaction->to_id)->toBeNull();
    expect($transaction->to_type)->toBeNull();
    expect($transaction->type)->toBe(TransactionType::WITHDRAW);
    expect($transaction->metadata->toArray())->toBe([
        "test" => true,
    ]);
    expect($transaction->amount)->toBe("5.00");
    expect($transaction->discount)->toBe("0.00");
    expect($transaction->fee)->toBe("0.00");
    expect($transaction->confirmed)->toBeFalse();
    expect($wallet->balance)->toBe("10.00");
});

it('can force withdraw', function () {
    /** @var Wallet $wallet */
    $wallet = Wallet::factory()->create();
    $wallet->deposit(10, ["test" => false]);
    $transaction = $wallet->forceWithdraw(10, ["test" => true]);

    expect($transaction->from_id)->toBe($wallet->id);
    expect($transaction->from_type)->toBe($wallet::class);
    expect($transaction->to_id)->toBeNull();
    expect($transaction->to_type)->toBeNull();
    expect($transaction->type)->toBe(TransactionType::WITHDRAW);
    expect($transaction->metadata->toArray())->toBe([
        "test" => true,
    ]);
    expect($transaction->amount)->toBe("10.00");
    expect($transaction->discount)->toBe("0.00");
    expect($transaction->fee)->toBe("0.00");
    expect($wallet->balance)->toBe("0.00");
});

it('cannot withdraw more than balance', function () {
    /** @var Wallet $wallet */
    $wallet = Wallet::factory()->create();
    $wallet->deposit(10, ["test" => false]);
    $transaction = $wallet->forceWithdraw(15, ["test" => true]);
})->throws(CannotWithdraw::class);

it('can transfer', function () {
    /** @var Wallet $wallet */
    /** @var Wallet $receiver */
    [$wallet, $receiver] = Wallet::factory(2)->create();
    $wallet->deposit(10, ["test" => false]);
    $transaction = $wallet->transfer($receiver, 5, ["transfer" => true]);

    expect($transaction->from_id)->toBe($wallet->id);
    expect($transaction->from_type)->toBe($wallet::class);
    expect($transaction->to_id)->toBe($receiver->id);
    expect($transaction->to_type)->toBe($receiver::class);
    expect($transaction->type)->toBe(TransactionType::TRANSFER);
    expect($transaction->metadata->toArray())->toBe([
        "transfer" => true,
    ]);
    expect($transaction->amount)->toBe("5.00");
    expect($transaction->discount)->toBe("0.00");
    expect($transaction->fee)->toBe("0.00");
    expect($wallet->balance)->toBe("5.00");
    expect($receiver->balance)->toBe("5.00");
});

it('cannot transfer more than balance', function () {
    /** @var Wallet $wallet */
    /** @var Wallet $receiver */
    [$wallet, $receiver] = Wallet::factory(2)->create();
    $wallet->deposit(10, ["test" => false]);
    $wallet->transfer($receiver, 15, ["transfer" => true]);
})->throws(CannotTransfer::class);

it('cannot transfer to non wallet model', function () {
    /** @var Wallet $wallet */
    /** @var Wallet $receiver */
    $wallet = Wallet::factory()->create();
    $product = new SampleFullProduct();
    $wallet->deposit(10, ["test" => false]);
    $wallet->transfer($product, 5, ["transfer" => true]);
})->throws(InvalidWalletModelProvided::class);

it('can run safe transfer', function () {
    /** @var Wallet $wallet */
    /** @var Wallet $receiver */
    [$wallet, $receiver] = Wallet::factory(2)->create();
    $wallet->deposit(10, ["test" => false]);
    $transaction = $wallet->safeTransfer($receiver, 5, ["transfer" => true]);
    $receiver = $receiver->fresh();

    expect($transaction->from_id)->toBe($wallet->id);
    expect($transaction->from_type)->toBe($wallet::class);
    expect($transaction->to_id)->toBe($receiver->id);
    expect($transaction->to_type)->toBe($receiver::class);
    expect($transaction->type)->toBe(TransactionType::TRANSFER);
    expect($transaction->metadata->toArray())->toBe([
        "transfer" => true,
    ]);
    expect($transaction->amount)->toBe("5.00");
    expect($transaction->discount)->toBe("0.00");
    expect($transaction->fee)->toBe("0.00");
    expect($wallet->balance)->toBe("5.00");
    expect($receiver->balance)->toBe("5.00");
});

it('can get null response from safe transfer', function () {
    /** @var Wallet $wallet */
    /** @var Wallet $receiver */
    $wallet = Wallet::factory(2)->create();
    $wallet->deposit(10, ["test" => false]);
    $product = new SampleFullProduct();

    $transaction = $wallet->safeTransfer($product, 5, ["transfer" => true]);
    expect($transaction)->toBeNull();
});

it('can run forced transfer', function () {
    /** @var Wallet $wallet */
    /** @var Wallet $receiver */
    [$wallet, $receiver] = Wallet::factory(2)->create();
    $wallet->deposit(10, ["test" => false]);
    $transaction = $wallet->forceTransfer($receiver, 5, ["transfer" => true]);
    $receiver = $receiver->fresh();

    expect($transaction->from_id)->toBe($wallet->id);
    expect($transaction->from_type)->toBe($wallet::class);
    expect($transaction->to_id)->toBe($receiver->id);
    expect($transaction->to_type)->toBe($receiver::class);
    expect($transaction->type)->toBe(TransactionType::TRANSFER);
    expect($transaction->metadata->toArray())->toBe([
        "transfer" => true,
    ]);
    expect($transaction->amount)->toBe("5.00");
    expect($transaction->discount)->toBe("0.00");
    expect($transaction->fee)->toBe("0.00");
    expect($wallet->balance)->toBe("5.00");
    expect($receiver->balance)->toBe("5.00");
});

it('can check if withdraw is enabled', function () {
    /** @var Wallet $wallet */
    $wallet = Wallet::factory()->create();
    $wallet->deposit(10, ["test" => false]);

    expect($wallet->canWithdraw(5))->toBeTrue();
    expect($wallet->canWithdraw(10))->toBeFalse();
    expect($wallet->canWithdraw(10, true))->toBeTrue();
    expect($wallet->canWithdraw(11, true))->toBeFalse();
});

it('can get all associated transactions', function () {
    /** @var Wallet $wallet */
    /** @var Wallet $w2 */
    [$wallet, $w2] = Wallet::factory(2)->create();
    $t0 = $wallet->deposit(1, ["0" => false]);
    $t1 = $wallet->deposit(2, ["1" => false]);
    $t2 = $wallet->deposit(3, ["2" => false]);
    $w2->deposit(100);
    $t3 = $w2->transfer($wallet, 10);
    $t4 = $wallet->withdraw(5, ["3" => false]);

    $transactions = $wallet->transactions()->get()->pluck("id");
    expect($transactions->count())->toBe(5);
    expect(in_array($t0->id, $transactions->toArray(), true))->toBeTrue();
    expect(in_array($t1->id, $transactions->toArray(), true))->toBeTrue();
    expect(in_array($t2->id, $transactions->toArray(), true))->toBeTrue();
    expect(in_array($t3->id, $transactions->toArray(), true))->toBeTrue();
    expect(in_array($t4->id, $transactions->toArray(), true))->toBeTrue();
});

it('can get all sent transactions', function () {
    /** @var Wallet $wallet */
    /** @var Wallet $w2 */
    [$wallet, $w2] = Wallet::factory(2)->create();
    $t0 = $wallet->deposit(1, ["0" => false]);
    $t1 = $wallet->deposit(2, ["1" => false]);
    $t2 = $wallet->deposit(3, ["2" => false]);
    $w2->deposit(100);
    $t3 = $w2->transfer($wallet, 10);
    $t4 = $wallet->withdraw(5, ["3" => false]);

    $transactions = $wallet->sentTransactions()->get()->pluck("id");
    expect($transactions->count())->toBe(1);
    expect(in_array($t4->id, $transactions->toArray(), true))->toBeTrue();
});



it('can get all received transactions', function () {
    /** @var Wallet $wallet */
    /** @var Wallet $w2 */
    [$wallet, $w2] = Wallet::factory(2)->create();
    $t0 = $wallet->deposit(1, ["0" => false]);
    $t1 = $wallet->deposit(2, ["1" => false]);
    $t2 = $wallet->deposit(3, ["2" => false]);
    $w2->deposit(100);
    $t3 = $w2->transfer($wallet, 10);
    $t4 = $wallet->withdraw(5, ["3" => false]);

    $transactions = $wallet->receivedTransactions()->get()->pluck("id");
    expect($transactions->count())->toBe(4);
    expect(in_array($t0->id, $transactions->toArray(), true))->toBeTrue();
    expect(in_array($t1->id, $transactions->toArray(), true))->toBeTrue();
    expect(in_array($t2->id, $transactions->toArray(), true))->toBeTrue();
    expect(in_array($t3->id, $transactions->toArray(), true))->toBeTrue();
});
