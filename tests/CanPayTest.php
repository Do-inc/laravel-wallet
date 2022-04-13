<?php

use Doinc\Wallet\Enums\TransactionType;
use Doinc\Wallet\Models\Wallet;
use Doinc\Wallet\Tests\Samples\SampleFullProduct;

it('can get a product for free', function () {
    /** @var Wallet $wallet */
    $wallet = Wallet::factory()->create();
    $wallet->deposit(10, ["test" => true]);
    $product = new SampleFullProduct();
    $transaction = $product->
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
