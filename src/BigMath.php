<?php

namespace Doinc\Wallet;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Doinc\Wallet\Enums\BigNumberComparisonStatus;

class BigMath
{
    protected const SCALE = 256;

    /**
     * Sum two numbers and returns the raw result
     *
     * @param int|float|string $first
     * @param int|float|string $second
     * @return string
     */
    public static function add(int|float|string $first, int|float|string $second): string
    {
        return (string)BigDecimal::of($first)
            ->plus(BigDecimal::of($second))
            ->toScale(self::SCALE, RoundingMode::DOWN);
    }

    /**
     * Subtract two numbers and returns the raw result
     *
     * @param int|float|string $first
     * @param int|float|string $second
     * @return string
     */
    public static function sub(int|float|string $first, int|float|string $second): string
    {
        return (string)BigDecimal::of($first)
            ->minus(BigDecimal::of($second))
            ->toScale(self::SCALE, RoundingMode::DOWN);
    }

    /**
     * Divides two numbers and returns the raw result
     *
     * @param int|float|string $first
     * @param int|float|string $second
     * @return string
     */
    public static function div(int|float|string $first, int|float|string $second): string
    {
        return (string)BigDecimal::of($first)
            ->dividedBy(BigDecimal::of($second), self::SCALE, RoundingMode::DOWN);
    }

    /**
     * Multiply two numbers and returns the raw result
     *
     * @param int|float|string $first
     * @param int|float|string $second
     * @return string
     */
    public static function mul(int|float|string $first, int|float|string $second): string
    {
        return (string)BigDecimal::of($first)
            ->multipliedBy(BigDecimal::of($second))
            ->toScale(self::SCALE, RoundingMode::DOWN);
    }

    /**
     * Pow the first number for the second one returning the raw result
     *
     * @param int|float|string $first
     * @param int $second
     * @return string
     */
    public static function pow(int|float|string $first, int $second): string
    {
        return (string)BigDecimal::of($first)
            ->power($second)
            ->toScale(self::SCALE, RoundingMode::DOWN);
    }

    /**
     * Pow 10 to the given exponent and returns the raw result
     *
     * @param int $number
     * @return string
     */
    public static function powTen(int $number): string
    {
        return self::pow(10, $number);
    }

    /**
     * Ceil the provided number and returns the raw result
     *
     * @param int|float|string $number
     * @return string
     */
    public static function ceil(int|float|string $number): string
    {
        return (string)BigDecimal::of($number)
            ->dividedBy(BigDecimal::one(), 0, RoundingMode::CEILING);
    }

    /**
     * Floor the provided number and returns the raw result
     *
     * @param int|float|string $number
     * @return string
     */
    public static function floor(int|float|string $number): string
    {
        return (string)BigDecimal::of($number)
            ->dividedBy(BigDecimal::one(), 0, RoundingMode::FLOOR);
    }

    /**
     * Rounds the provided number and returns the raw result
     *
     * @param int|float|string $number
     * @param int $precision
     * @return string
     */
    public static function round(int|float|string $number, int $precision = 0): string
    {
        return (string)BigDecimal::of($number)
            ->dividedBy(BigDecimal::one(), $precision, RoundingMode::HALF_UP);
    }

    /**
     * Get the absolute value of the provided number and returns the raw result
     *
     * @param int|float|string $number
     * @return string
     */
    public static function abs(int|float|string $number): string
    {
        return (string)BigDecimal::of($number)->abs();
    }

    /**
     * Negate the provided number and returns the raw result
     *
     * @param int|float|string $number
     * @return string
     */
    public static function negative(int|float|string $number): string
    {
        return (string)BigDecimal::of($number)->negated();
    }

    /**
     * Compares two instances of bignumber stating which relation has the first with the second
     *
     * @param int|float|string $first
     * @param int|float|string $second
     * @return BigNumberComparisonStatus
     */
    public static function compare(int|float|string $first, int|float|string $second): BigNumberComparisonStatus
    {
        return BigNumberComparisonStatus::from(
            BigDecimal::of($first)->compareTo(BigDecimal::of($second))
        );
    }

    /**
     * Checks whether the first number is lower than the second one
     *
     * @param int|float|string $first
     * @param int|float|string $second
     * @return bool
     */
    public static function lowerThan(int|float|string $first, int|float|string $second): bool
    {
        return self::compare($first, $second) === BigNumberComparisonStatus::LOWER_THAN;
    }

    /**
     * Checks whether two numbers are equal
     *
     * @param int|float|string $first
     * @param int|float|string $second
     * @return bool
     */
    public static function equal(int|float|string $first, int|float|string $second): bool
    {
        return self::compare($first, $second) === BigNumberComparisonStatus::EQUAL;
    }

    /**
     * Checks whether the first number is higher than the second one
     *
     * @param int|float|string $first
     * @param int|float|string $second
     * @return bool
     */
    public static function higherThan(int|float|string $first, int|float|string $second): bool
    {
        return self::compare($first, $second) === BigNumberComparisonStatus::HIGHER_THAN;
    }
}
