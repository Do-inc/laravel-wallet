<?php

use Doinc\Wallet\Enums\TransactionType;
use Doinc\Wallet\Models\Wallet;

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
