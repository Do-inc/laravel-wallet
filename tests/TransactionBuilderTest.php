<?php

use Doinc\Wallet\Enums\TransactionType;
use Doinc\Wallet\Models\Wallet;
use Doinc\Wallet\Tests\Samples\SampleDiscountableProduct;
use Doinc\Wallet\Tests\Samples\SampleFullProduct;
use Doinc\Wallet\Tests\Samples\SampleMinimalTaxableProduct;
use Doinc\Wallet\Tests\Samples\SampleProduct;
use Doinc\Wallet\Tests\Samples\SampleTaxableProduct;
use Doinc\Wallet\TransactionBuilder;

it('can create full transaction', function () {
    /** @var Wallet $from */
    $from = Wallet::factory()->create();
    /** @var Wallet $to */
    $to = Wallet::factory()->create();

    $transaction = TransactionBuilder::init()
        ->from($from)
        ->to($to)
        ->withType(TransactionType::TRANSFER)
        ->withMetadata([
            "test" => true,
        ])
        ->withAmount("123")
        ->withDiscount("1.2")
        ->withTax("2.3")
        ->get();
    $transaction->save();
    $transaction = $transaction->fresh();

    expect($transaction->from_id)->toBe($from->id);
    expect($transaction->from_type)->toBe($from::class);
    expect($transaction->to_id)->toBe($to->id);
    expect($transaction->to_type)->toBe($to::class);
    expect($transaction->type)->toBe(TransactionType::TRANSFER);
    expect($transaction->metadata->toArray())->toBe([
        "test" => true,
    ]);
    expect($transaction->amount)->toBe("123.00");
    expect($transaction->discount)->toBe("1.20");
    expect($transaction->fee)->toBe("2.30");
});

it('can create transaction without receiver (withdraw)', function () {
    /** @var Wallet $from */
    $from = Wallet::factory()->create();
    /** @var Wallet $to */
    $to = Wallet::factory()->create();

    $transaction = TransactionBuilder::init()
        ->from($from)
        ->withType(TransactionType::WITHDRAW)
        ->withMetadata([
            "test" => true,
        ])
        ->withAmount("123")
        ->withDiscount("1.2")
        ->withTax("2.3")
        ->get();
    $transaction->save();
    $transaction = $transaction->fresh();

    expect($transaction->from_id)->toBe($from->id);
    expect($transaction->from_type)->toBe($from::class);
    expect($transaction->to_id)->toBeNull();
    expect($transaction->to_type)->toBeNull();
    expect($transaction->type)->toBe(TransactionType::WITHDRAW);
    expect($transaction->metadata->toArray())->toBe([
        "test" => true,
    ]);
    expect($transaction->amount)->toBe("123.00");
    expect($transaction->discount)->toBe("1.20");
    expect($transaction->fee)->toBe("2.30");
});

it('can create transaction without sender (deposit)', function () {
    /** @var Wallet $from */
    $from = Wallet::factory()->create();
    /** @var Wallet $to */
    $to = Wallet::factory()->create();

    $transaction = TransactionBuilder::init()
        ->to($to)
        ->withType(TransactionType::DEPOSIT)
        ->withMetadata([
            "test" => true,
        ])
        ->withAmount("123")
        ->withDiscount("1.2")
        ->withTax("2.3")
        ->get();
    $transaction->save();
    $transaction = $transaction->fresh();

    expect($transaction->to_id)->toBe($to->id);
    expect($transaction->to_type)->toBe($to::class);
    expect($transaction->from_id)->toBeNull();
    expect($transaction->from_type)->toBeNull();
    expect($transaction->type)->toBe(TransactionType::DEPOSIT);
    expect($transaction->metadata->toArray())->toBe([
        "test" => true,
    ]);
    expect($transaction->amount)->toBe("123.00");
    expect($transaction->discount)->toBe("1.20");
    expect($transaction->fee)->toBe("2.30");
});

it('can create transaction using product (payment)', function () {
    /** @var Wallet $from */
    $from = Wallet::factory()->create();
    /** @var Wallet $to */
    $to = Wallet::factory()->create();

    $product = new SampleProduct();

    $transaction = TransactionBuilder::init()
        ->from($from)
        ->to($product)
        ->withType(TransactionType::PAYMENT)
        ->syncWithProductMetadata()
        ->isConfirmed(false)
        ->get(compute_cost_from_product: true);
    $transaction->save();
    $transaction = $transaction->fresh();

    expect($transaction->from_id)->toBe($from->id);
    expect($transaction->from_type)->toBe($from::class);
    expect($transaction->to_id)->toBeNull();
    expect($transaction->to_type)->toBe($product::class);
    expect($transaction->type)->toBe(TransactionType::PAYMENT);
    expect($transaction->metadata->toArray())->toBe([]);
    expect($transaction->amount)->toBe("1000.00");
    expect($transaction->discount)->toBe("0.00");
    expect($transaction->fee)->toBe("0.00");
    expect($transaction->confirmed)->toBe(false);
    expect($transaction->confirmed_at)->toBeNull();
});

it('can create transaction using product (refund)', function () {
    /** @var Wallet $from */
    $from = Wallet::factory()->create();
    /** @var Wallet $to */
    $to = Wallet::factory()->create();

    $product = new SampleProduct();

    $transaction = TransactionBuilder::init()
        ->from($product)
        ->to($to)
        ->withType(TransactionType::REFUND)
        ->syncWithProductMetadata()
        ->isConfirmed()
        ->get(compute_cost_from_product: true);
    $transaction->save();
    $transaction = $transaction->fresh();

    expect($transaction->to_id)->toBe($to->id);
    expect($transaction->to_type)->toBe($to::class);
    expect($transaction->from_id)->toBeNull();
    expect($transaction->from_type)->toBe($product::class);
    expect($transaction->type)->toBe(TransactionType::REFUND);
    expect($transaction->metadata->toArray())->toBe([]);
    expect($transaction->amount)->toBe("1000.00");
    expect($transaction->discount)->toBe("0.00");
    expect($transaction->fee)->toBe("0.00");
    expect($transaction->confirmed)->toBe(true);
    expect($transaction->confirmed_at)->not()->toBeNull();
});

it('can create transaction using discountable product (payment)', function () {
    /** @var Wallet $from */
    $from = Wallet::factory()->create();
    /** @var Wallet $to */
    $to = Wallet::factory()->create();

    $product = new SampleDiscountableProduct();

    $transaction = TransactionBuilder::init()
        ->from($from)
        ->to($product)
        ->withType(TransactionType::PAYMENT)
        ->syncWithProductMetadata()
        ->isConfirmed(false)
        ->get(compute_cost_from_product: true);
    $transaction->save();
    $transaction = $transaction->fresh();

    expect($transaction->from_id)->toBe($from->id);
    expect($transaction->from_type)->toBe($from::class);
    expect($transaction->to_id)->toBeNull();
    expect($transaction->to_type)->toBe($product::class);
    expect($transaction->type)->toBe(TransactionType::PAYMENT);
    expect($transaction->metadata->toArray())->toBe([]);
    expect($transaction->amount)->toBe("1000.00");
    expect($transaction->discount)->toBe("100.00");
    expect($transaction->fee)->toBe("0.00");
    expect($transaction->confirmed)->toBe(false);
    expect($transaction->confirmed_at)->toBeNull();
});

it('can create transaction using taxable product (payment)', function () {
    /** @var Wallet $from */
    $from = Wallet::factory()->create();
    /** @var Wallet $to */
    $to = Wallet::factory()->create();

    $product = new SampleTaxableProduct();

    $transaction = TransactionBuilder::init()
        ->from($from)
        ->to($product)
        ->withType(TransactionType::PAYMENT)
        ->syncWithProductMetadata()
        ->isConfirmed(false)
        ->get(compute_cost_from_product: true);
    $transaction->save();
    $transaction = $transaction->fresh();

    expect($transaction->from_id)->toBe($from->id);
    expect($transaction->from_type)->toBe($from::class);
    expect($transaction->to_id)->toBeNull();
    expect($transaction->to_type)->toBe($product::class);
    expect($transaction->type)->toBe(TransactionType::PAYMENT);
    expect($transaction->metadata->toArray())->toBe([]);
    expect($transaction->amount)->toBe("1000.00");
    expect($transaction->discount)->toBe("0.00");
    expect($transaction->fee)->toBe("50.00");
    expect($transaction->confirmed)->toBe(false);
    expect($transaction->confirmed_at)->toBeNull();
});

it('can create transaction using minimal taxable product (payment)', function () {
    /** @var Wallet $from */
    $from = Wallet::factory()->create();
    /** @var Wallet $to */
    $to = Wallet::factory()->create();

    $product = new SampleMinimalTaxableProduct();

    $transaction = TransactionBuilder::init()
        ->from($from)
        ->to($product)
        ->withType(TransactionType::PAYMENT)
        ->syncWithProductMetadata()
        ->isConfirmed(false)
        ->get(compute_cost_from_product: true);
    $transaction->save();
    $transaction = $transaction->fresh();

    expect($transaction->from_id)->toBe($from->id);
    expect($transaction->from_type)->toBe($from::class);
    expect($transaction->to_id)->toBeNull();
    expect($transaction->to_type)->toBe($product::class);
    expect($transaction->type)->toBe(TransactionType::PAYMENT);
    expect($transaction->metadata->toArray())->toBe([]);
    expect($transaction->amount)->toBe("1000.00");
    expect($transaction->discount)->toBe("0.00");
    expect($transaction->fee)->toBe("70.00");
    expect($transaction->confirmed)->toBe(false);
    expect($transaction->confirmed_at)->toBeNull();
});

it('can create transaction using full product (payment)', function () {
    /** @var Wallet $from */
    $from = Wallet::factory()->create();
    /** @var Wallet $to */
    $to = Wallet::factory()->create();

    $product = new SampleFullProduct();

    $transaction = TransactionBuilder::init()
        ->from($from)
        ->to($product)
        ->withType(TransactionType::PAYMENT)
        ->syncWithProductMetadata()
        ->isConfirmed(false)
        ->get(compute_cost_from_product: true);
    $transaction->save();
    $transaction = $transaction->fresh();

    expect($transaction->from_id)->toBe($from->id);
    expect($transaction->from_type)->toBe($from::class);
    expect($transaction->to_id)->toBeNull();
    expect($transaction->to_type)->toBe($product::class);
    expect($transaction->type)->toBe(TransactionType::PAYMENT);
    expect($transaction->metadata->toArray())->toBe([]);
    expect($transaction->amount)->toBe("1000.00");
    expect($transaction->discount)->toBe("102.00");
    expect($transaction->fee)->toBe("75.00");
    expect($transaction->confirmed)->toBe(false);
    expect($transaction->confirmed_at)->toBeNull();
});

it('can create transaction without loading product data', function () {
    /** @var Wallet $from */
    $from = Wallet::factory()->create();
    /** @var Wallet $to */
    $to = Wallet::factory()->create();

    $product = new SampleFullProduct();

    $transaction = TransactionBuilder::init()
        ->from($from)
        ->to($product)
        ->withType(TransactionType::PAYMENT)
        ->syncWithProductMetadata()
        ->isConfirmed(false)
        ->get();
    $transaction->save();
    $transaction = $transaction->fresh();

    expect($transaction->from_id)->toBe($from->id);
    expect($transaction->from_type)->toBe($from::class);
    expect($transaction->to_id)->toBeNull();
    expect($transaction->to_type)->toBe($product::class);
    expect($transaction->type)->toBe(TransactionType::PAYMENT);
    expect($transaction->metadata->toArray())->toBe([]);
    expect($transaction->amount)->toBe("0.00");
    expect($transaction->discount)->toBe("0.00");
    expect($transaction->fee)->toBe("0.00");
    expect($transaction->confirmed)->toBe(false);
    expect($transaction->confirmed_at)->toBeNull();
});
