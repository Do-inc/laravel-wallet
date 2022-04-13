<?php

use Doinc\Wallet\BigMath;
use Doinc\Wallet\Enums\BigNumberComparisonStatus;

it('can sum numbers', function () {
    expect(BigMath::add(1, 1))->toBe("2.00");
    expect(BigMath::add(1, 1.1))->toBe("2.10");
    expect(BigMath::add(1, 1.11))->toBe("2.11");
    expect(BigMath::add(1, 1.111))->toBe("2.11");
    expect(BigMath::add(1, 1.119))->toBe("2.11");
    expect(BigMath::add(1.0, 1.119))->toBe("2.11");
    expect(BigMath::add("1", "1.119"))->toBe("2.11");
    expect(BigMath::add("1.00", "1.119"))->toBe("2.11");
});

it('can subtract numbers', function () {
    expect(BigMath::sub(2, 1))->toBe("1.00");
    expect(BigMath::sub(2, 1.1))->toBe("0.90");
    expect(BigMath::sub(2, 1.11))->toBe("0.89");

    // Rounds down!!
    expect(BigMath::sub(2, 1.111))->toBe("0.88");
    expect(BigMath::sub(2, 1.119))->toBe("0.88");
    expect(BigMath::sub(2.0, 1.119))->toBe("0.88");
    expect(BigMath::sub("2", "1.119"))->toBe("0.88");
    expect(BigMath::sub("2.00", "1.119"))->toBe("0.88");
});

it('can divide numbers', function () {
    expect(BigMath::div(2, 1))->toBe("2.00");
    expect(BigMath::div(2, 2.0))->toBe("1.00");
    expect(BigMath::div(5, "2"))->toBe("2.50");
    expect(BigMath::div(3, 2))->toBe("1.50");
    expect(BigMath::div(2, 3))->toBe("0.66");
});

it('can multiply numbers', function () {
    expect(BigMath::mul(2, 1))->toBe("2.00");
    expect(BigMath::mul(2, 2.0))->toBe("4.00");
    expect(BigMath::mul(1.32, "2"))->toBe("2.64");
    expect(BigMath::mul(100, 22))->toBe("2200.00");
});

it('can pow numbers', function () {
    expect(BigMath::pow(2, 1))->toBe("2.00");
    expect(BigMath::pow(2, 0))->toBe("1.00");
    expect(BigMath::pow(2.5, "2"))->toBe("6.25");
});

it('can pow 10', function () {
    expect(BigMath::powTen(2))->toBe("100.00");
    expect(BigMath::powTen("3"))->toBe("1000.00");
    expect(BigMath::powTen(0))->toBe("1.00");
});

it('can ceil', function () {
    expect(BigMath::ceil(-1.2))->toBe("-1");
    expect(BigMath::ceil("1.33"))->toBe("2");
});

it('can floor', function () {
    expect(BigMath::floor(-1.2))->toBe("-2");
    expect(BigMath::floor("1.33"))->toBe("1");
});

it('can round', function () {
    expect(BigMath::round(-1.2))->toBe("-1");
    expect(BigMath::round("1.33"))->toBe("1");
    expect(BigMath::round("1.53"))->toBe("2");
    expect(BigMath::round(-1.53))->toBe("-2");
});

it('can return the absolute number', function () {
    expect(BigMath::abs(-1.2))->toBe("1.20");
    expect(BigMath::abs("1.33"))->toBe("1.33");
});

it('can return the negative number', function () {
    expect(BigMath::negative(-1.2))->toBe("1.20");
    expect(BigMath::negative("1.33"))->toBe("-1.33");
});

it('can compare numbers', function () {
    expect(BigMath::compare(1, 1.0001))->toBe(BigNumberComparisonStatus::LOWER_THAN);
    expect(BigMath::compare(1, "1." . str_repeat("0", 100) . "1"))
        ->toBe(BigNumberComparisonStatus::LOWER_THAN);
    expect(BigMath::compare(1, 2))->toBe(BigNumberComparisonStatus::LOWER_THAN);
    expect(BigMath::compare(2, 2))->toBe(BigNumberComparisonStatus::EQUAL);
    expect(BigMath::compare(3, 2))->toBe(BigNumberComparisonStatus::HIGHER_THAN);
    expect(BigMath::compare(2.0001, 2))->toBe(BigNumberComparisonStatus::HIGHER_THAN);
    expect(BigMath::compare("2." . str_repeat("0", 100) . "1", 2))
        ->toBe(BigNumberComparisonStatus::HIGHER_THAN);
});

it('can identify correctly "lower than" relation', function () {
    expect(BigMath::lowerThan(1, 1.0001))->toBeTrue();
    expect(BigMath::lowerThan(1, "1." . str_repeat("0", 100) . "1"))->toBeTrue();
    expect(BigMath::lowerThan(1, 2))->toBeTrue();
    expect(BigMath::lowerThan(2, 2))->toBeFalse();
    expect(BigMath::lowerThan(3, 2))->toBeFalse();
    expect(BigMath::lowerThan(2.0001, 2))->toBeFalse();
    expect(BigMath::lowerThan("2." . str_repeat("0", 100) . "1", 2))->toBeFalse();
});

it('can identify correctly "equal" relation', function () {
    expect(BigMath::equal(1, 1.0001))->toBeFalse();
    expect(BigMath::equal(1, "1." . str_repeat("0", 100) . "1"))->toBeFalse();
    expect(BigMath::equal(1, 2))->toBeFalse();
    expect(BigMath::equal(2, 2))->toBeTrue();
    expect(BigMath::equal(3, 2))->toBeFalse();
    expect(BigMath::equal(2.0001, 2))->toBeFalse();
    expect(BigMath::equal("2." . str_repeat("0", 100) . "1", 2))->toBeFalse();
});

it('can identify correctly "higher than" relation', function () {
    expect(BigMath::higherThan(1, 1.0001))->toBeFalse();
    expect(BigMath::higherThan(1, "1." . str_repeat("0", 100) . "1"))->toBeFalse();
    expect(BigMath::higherThan(1, 2))->toBeFalse();
    expect(BigMath::higherThan(2, 2))->toBeFalse();
    expect(BigMath::higherThan(3, 2))->toBeTrue();
    expect(BigMath::higherThan(2.0001, 2))->toBeTrue();
    expect(BigMath::higherThan("2." . str_repeat("0", 100) . "1", 2))->toBeTrue();
});
