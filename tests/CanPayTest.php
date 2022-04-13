<?php

use Doinc\Wallet\Enums\TransactionType;
use Doinc\Wallet\Exceptions\CannotBuyProduct;
use Doinc\Wallet\Exceptions\CannotPay;
use Doinc\Wallet\Exceptions\CannotRefundUnpaidProduct;
use Doinc\Wallet\Models\Wallet;
use Doinc\Wallet\Tests\Samples\SampleFullProduct;
use Doinc\Wallet\Tests\Samples\SampleFullUnbuyableProduct;

it('can get a product for free', function () {
    /** @var Wallet $wallet */
    $wallet = Wallet::factory()->create();
    $wallet->deposit(10);
    $product = new SampleFullProduct();
    $transaction = $wallet->payFree($product);

    expect($transaction->from_id)->toBe($wallet->id);
    expect($transaction->from_type)->toBe($wallet::class);
    expect($transaction->to_id)->toBeNull();
    expect($transaction->to_type)->toBe($product::class);
    expect($transaction->type)->toBe(TransactionType::PAYMENT);
    expect($transaction->metadata->toArray())->toBe([]);
    expect($transaction->amount)->toBe("0.00");
    expect($transaction->discount)->toBe("0.00");
    expect($transaction->fee)->toBe("0.00");
    expect($wallet->balance)->toBe("10.00");
});

it('cannot buy a product for free if not allowed', function () {
    /** @var Wallet $wallet */
    $wallet = Wallet::factory()->create();
    $wallet->deposit(10);
    $product = new SampleFullUnbuyableProduct();
    $wallet->payFree($product);
})->throws(CannotBuyProduct::class);

it('can run a safe payment', function () {
    /** @var Wallet $wallet */
    $wallet = Wallet::factory()->create();
    $wallet->deposit(1500);
    $product = new SampleFullProduct();
    $transaction = $wallet->safePay($product);

    expect($transaction->from_id)->toBe($wallet->id);
    expect($transaction->from_type)->toBe($wallet::class);
    expect($transaction->to_id)->toBeNull();
    expect($transaction->to_type)->toBe($product::class);
    expect($transaction->type)->toBe(TransactionType::PAYMENT);
    expect($transaction->metadata->toArray())->toBe([]);
    expect($transaction->amount)->toBe("1000.00");
    expect($transaction->discount)->toBe("102.00");
    expect($transaction->fee)->toBe("75.00");
    expect($wallet->balance)->toBe("527.00");
});

it('can get null result if a safe payment fails', function () {
    /** @var Wallet $wallet */
    $wallet = Wallet::factory()->create();
    $wallet->deposit(10);
    $product = new SampleFullProduct();
    $transaction = $wallet->safePay($product);

    expect($transaction)->toBeNull();
});

it('can pay', function () {
    /** @var Wallet $wallet */
    $wallet = Wallet::factory()->create();
    $wallet->deposit(1500);
    $product = new SampleFullProduct();
    $transaction = $wallet->pay($product);

    expect($transaction->from_id)->toBe($wallet->id);
    expect($transaction->from_type)->toBe($wallet::class);
    expect($transaction->to_id)->toBeNull();
    expect($transaction->to_type)->toBe($product::class);
    expect($transaction->type)->toBe(TransactionType::PAYMENT);
    expect($transaction->metadata->toArray())->toBe([]);
    expect($transaction->amount)->toBe("1000.00");
    expect($transaction->discount)->toBe("102.00");
    expect($transaction->fee)->toBe("75.00");
    expect($wallet->balance)->toBe("527.00");
});

it('cannot pay without enough funds', function () {
    /** @var Wallet $wallet */
    $wallet = Wallet::factory()->create();
    $wallet->deposit(10);
    $product = new SampleFullProduct();
    $wallet->pay($product);
})->throws(CannotPay::class);

it('cannot pay if not allowed', function () {
    /** @var Wallet $wallet */
    $wallet = Wallet::factory()->create();
    $wallet->deposit(1500);
    $product = new SampleFullUnbuyableProduct();
    $wallet->pay($product);
})->throws(CannotBuyProduct::class);

it('can force payment', function () {
    /** @var Wallet $wallet */
    $wallet = Wallet::factory()->create();
    $wallet->deposit(1500);
    $product = new SampleFullProduct();
    $transaction = $wallet->forcePay($product);

    expect($transaction->from_id)->toBe($wallet->id);
    expect($transaction->from_type)->toBe($wallet::class);
    expect($transaction->to_id)->toBeNull();
    expect($transaction->to_type)->toBe($product::class);
    expect($transaction->type)->toBe(TransactionType::PAYMENT);
    expect($transaction->metadata->toArray())->toBe([]);
    expect($transaction->amount)->toBe("1000.00");
    expect($transaction->discount)->toBe("102.00");
    expect($transaction->fee)->toBe("75.00");
    expect($wallet->balance)->toBe("527.00");
});

it('can refund', function () {
    /** @var Wallet $wallet */
    $wallet = Wallet::factory()->create();
    $wallet->deposit(1500);
    $product = new SampleFullProduct();
    $wallet->pay($product);
    $transaction = $wallet->refund($product);

    expect($transaction->to_id)->toBe($wallet->id);
    expect($transaction->to_type)->toBe($wallet::class);
    expect($transaction->from_id)->toBeNull();
    expect($transaction->from_type)->toBe($product::class);
    expect($transaction->type)->toBe(TransactionType::REFUND);
    expect($transaction->metadata->toArray())->toBe([]);
    expect($transaction->amount)->toBe("1000.00");
    expect($transaction->discount)->toBe("102.00");
    expect($transaction->fee)->toBe("75.00");
    expect($wallet->balance)->toBe("1425.00");
});

it('cannot refund unpaid product', function () {
    /** @var Wallet $wallet */
    $wallet = Wallet::factory()->create();
    $wallet->deposit(1500);
    $product = new SampleFullProduct();
    $wallet->refund($product);
})->throws(CannotRefundUnpaidProduct::class);

it('can run safe refund', function () {
    /** @var Wallet $wallet */
    $wallet = Wallet::factory()->create();
    $wallet->deposit(1500);
    $product = new SampleFullProduct();
    $wallet->pay($product);
    $transaction = $wallet->safeRefund($product);

    expect($transaction->to_id)->toBe($wallet->id);
    expect($transaction->to_type)->toBe($wallet::class);
    expect($transaction->from_id)->toBeNull();
    expect($transaction->from_type)->toBe($product::class);
    expect($transaction->type)->toBe(TransactionType::REFUND);
    expect($transaction->metadata->toArray())->toBe([]);
    expect($transaction->amount)->toBe("1000.00");
    expect($transaction->discount)->toBe("102.00");
    expect($transaction->fee)->toBe("75.00");
    expect($wallet->balance)->toBe("1425.00");
});

it('can get null response when safe refund fails', function () {
    /** @var Wallet $wallet */
    $wallet = Wallet::factory()->create();
    $wallet->deposit(1500);
    $product = new SampleFullProduct();
    $transaction = $wallet->safeRefund($product);

    expect($transaction)->toBeNull();
});

it('can force refund', function () {
    /** @var Wallet $wallet */
    $wallet = Wallet::factory()->create();
    $wallet->deposit(1500);
    $product = new SampleFullProduct();
    $wallet->pay($product);
    $transaction = $wallet->forceRefund($product);

    expect($transaction->to_id)->toBe($wallet->id);
    expect($transaction->to_type)->toBe($wallet::class);
    expect($transaction->from_id)->toBeNull();
    expect($transaction->from_type)->toBe($product::class);
    expect($transaction->type)->toBe(TransactionType::REFUND);
    expect($transaction->metadata->toArray())->toBe([]);
    expect($transaction->amount)->toBe("1000.00");
    expect($transaction->discount)->toBe("102.00");
    expect($transaction->fee)->toBe("75.00");
    expect($wallet->balance)->toBe("1425.00");
});

it('can get product payment', function () {
    /** @var Wallet $wallet */
    $wallet = Wallet::factory()->create();
    $wallet->deposit(1500);
    $product = new SampleFullProduct();
    $t0 = $wallet->pay($product);
    $transaction = $wallet->getPayment($product);

    expect($transaction->to_id)->toBe($t0->to_id);
    expect($transaction->to_type)->toBe($t0->to_type);
    expect($transaction->from_id)->toBe($t0->from_id);
    expect($transaction->from_type)->toBe($t0->from_type);
    expect($transaction->type)->toBe($t0->type);
    expect($transaction->metadata->toArray())->toBe($t0->metadata->toArray());
    expect($transaction->amount)->toBe($t0->amount);
    expect($transaction->discount)->toBe($t0->discount);
    expect($transaction->fee)->toBe($t0->fee);
});

it('can get all product payments', function () {
    /** @var Wallet $wallet */
    $wallet = Wallet::factory()->create();
    $wallet->deposit(3000);
    $product = new SampleFullProduct();
    $t0 = $wallet->pay($product);
    $t1 = $wallet->pay($product);
    $transaction = $wallet->getAllPayments($product);

    expect($transaction->count())->toBe(2);
    expect($transaction[0]->to_id)->toBe($t0->to_id);
    expect($transaction[0]->to_type)->toBe($t0->to_type);
    expect($transaction[0]->from_id)->toBe($t0->from_id);
    expect($transaction[0]->from_type)->toBe($t0->from_type);
    expect($transaction[0]->type)->toBe($t0->type);
    expect($transaction[0]->metadata->toArray())->toBe($t0->metadata->toArray());
    expect($transaction[0]->amount)->toBe($t0->amount);
    expect($transaction[0]->discount)->toBe($t0->discount);
    expect($transaction[0]->fee)->toBe($t0->fee);
    expect($transaction[1]->to_id)->toBe($t1->to_id);
    expect($transaction[1]->to_type)->toBe($t1->to_type);
    expect($transaction[1]->from_id)->toBe($t1->from_id);
    expect($transaction[1]->from_type)->toBe($t1->from_type);
    expect($transaction[1]->type)->toBe($t1->type);
    expect($transaction[1]->metadata->toArray())->toBe($t1->metadata->toArray());
    expect($transaction[1]->amount)->toBe($t1->amount);
    expect($transaction[1]->discount)->toBe($t1->discount);
    expect($transaction[1]->fee)->toBe($t1->fee);
});

it('can check if product was paid', function () {
    /** @var Wallet $wallet */
    $wallet = Wallet::factory()->create();
    $wallet->deposit(1500);
    $product = new SampleFullProduct();

    expect($wallet->paid($product))->toBeFalse();

    $wallet->pay($product);

    expect($wallet->paid($product))->toBeTrue();
});
