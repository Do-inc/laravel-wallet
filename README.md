
[![Laravel Wallet - by Do Group LLC](assets/default.png)](https://do-inc.co)

[![Tests](https://github.com/Do-inc/laravel-wallet/actions/workflows/run-tests.yml/badge.svg)](https://github.com/Do-inc/laravel-wallet/actions/workflows/run-tests.yml)
[![Check & fix styling](https://github.com/Do-inc/laravel-wallet/actions/workflows/php-cs-fixer.yml/badge.svg)](https://github.com/Do-inc/laravel-wallet/actions/workflows/php-cs-fixer.yml)
[![Coverage](https://img.shields.io/endpoint?url=https://gist.githubusercontent.com/ebalo55/45584b0dc38ce224d546245721105bdf/raw/laravel_wallet_package-main.json)](https://github.com/Do-inc/laravel-wallet/actions/workflows/run-coverage.yml)

## Installation

You can install the package via composer:

```bash
composer require do-inc/laravel-wallet
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="wallet-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="wallet-config"
```

This is the contents of the published config file:

```php
return [
    /*
     * |--------------------------------------------------------------------------
     * | Enable UUIDs
     * |--------------------------------------------------------------------------
     * |
     * | Enable uuids in migrations
     * |
     */
    "uuid" => false,

    /*
     * |--------------------------------------------------------------------------
     * | Decimal precision
     * |--------------------------------------------------------------------------
     * |
     * | Define the decimal precision used while doing calculus.
     * | Global is applied in during database creation and defines the default
     * | number of decimals for the wallets.
     * | Fee is the default number of decimals applied in taxable models.
     * | Discount is the default number of decimals applied in discountable models.
     * |
     */
    "precision" => [
        "global" => 2,
        "tax" => 2,
        "discount" => 2,
    ],

    /*
     * |--------------------------------------------------------------------------
     * | Wallet related errors
     * |--------------------------------------------------------------------------
     * |
     * | Defines the wallet error messages and codes.
     * | NOTE: Each of the code should be unique for a quick and easier
     * |       identification of flaws
     * |
     */
    "errors" => [
        "TRANSACTION_ALREADY_CONFIRMED" => [
            "message" => "Transaction already confirmed",
            "code" => 1000,
        ],
        "INVALID_WALLET_MODEL_PROVIDED" => [
            "message" => "Invalid model provided, expected wallet",
            "code" => 1001,
        ],
        "INVALID_WALLET_OWNER" => [
            "message" => "Invalid wallet owner, operation not allowed",
            "code" => 1002,
        ],
        "CANNOT_BUY_PRODUCT" => [
            "message" => "Cannot buy the provided product",
            "code" => 1003,
        ],
        "UNABLE_TO_CREATE_TRANSACTION" => [
            "message" => "Something went wrong during the creation of transaction",
            "code" => 1004,
        ],
        "CANNOT_WITHDRAW" => [
            "message" => "Cannot withdraw, balance is not enough",
            "code" => 1005,
        ],
        "CANNOT_TRANSFER" => [
            "message" => "Cannot transfer, balance is not enough",
            "code" => 1006,
        ],
        "CANNOT_PAY" => [
            "message" => "Cannot pay, balance is not enough",
            "code" => 1007,
        ],
        "CANNOT_REFUND_UNPAID_PRODUCT" => [
            "message" => "Cannot refund, the product have never been bought",
            "code" => 1008,
        ],
    ],
];
```

## Usage

### Basic
To start using this package add to your user model the `HasWallet` trait and the `Wallet` interface
```php
use Doinc\Wallet\Traits\HasWallet;
use Doinc\Wallet\Interfaces\Wallet;

class User extends Model implements Wallet
{
    use HasWallet;
    
    // ...
}
```

You can immediately start interacting with your new wallet.
```php
$user = User::first();
$receiver = User::last();

// Get user's balance
echo $user->balance . PHP_EOL; // "0"

// Deposit funds to user's wallet
$user->deposit(10);
$user->deposit(10.5, ["some" => "metadata"]);
$user->deposit("9.5", [], false); // transaction created in pending state, needs confirmation prior to be shown in the available balance
echo $user->balance . PHP_EOL;    // "20.50"

// Withdraw funds from user's wallet
$user->withdraw(2);
$user->withdraw(3.5, ["some" => "metadata"]);
$user->withdraw("4.5", [], false); // transaction created in pending state, needs confirmation prior to be shown in the available balance
echo $user->balance . PHP_EOL; // "15.00"

// Forcefully confirm withdraw transactions
$user->forceWithdraw(1);
$user->forceWithdraw("2", ["some" => "metadata"]);
echo $user->balance . PHP_EOL; // "12.00"

// Transfer funds from one wallet to another
$user->transfer($receiver, 1, ["some" => "metadata"]);
$user->safeTransfer($receiver, 1, ["some" => "metadata"]); // Silence all exception, returning null in case of error
$user->forceTransfer($receiver, 1, ["some" => "metadata"]);

// Check whether the balance is enough to withdraw the provided amount
$user->canWithdraw(1000); // false
$user->canWithdraw(0.5);  // true

// Retrieve transactions (included pending)
$user->transactions();          // all transactions
$user->sentTransactions();      // sent transactions (withdraws will be showed only in this query)
$user->receivedTransactions();  // received transactions (deposits will be showed only in this query)
```

### Confirmable transactions
By default, all transactions are created as confirmed, you can appropriately set the confirmation flag as you need for 
each transaction.

In order to confirm pending transaction you should implement `Confirmable` interface and use `CanConfirm` trait. 
```php
use Doinc\Wallet\Traits\HasWallet;
use Doinc\Wallet\Traits\CanConfirm;
use Doinc\Wallet\Interfaces\Wallet;
use Doinc\Wallet\Interfaces\Confirmable;

class User extends Model implements Wallet, Confirmable
{
    use HasWallet, CanConfirm;
    
    // ...
}
```

Confirming transactions is as easy as interacting with the wallet.
```php
$user = User::first();

// Populate the wallet balance to avoid exceptions
$user->deposit(10);

// Create a pending withdraw transaction
$transaction = $user->withdraw(2, confirmed: false);

// Confirm the pending transaction
$user->confirm($transaction); // or
$user->safeConfirm($transaction); // will return false in case of exceptions

// You can also reset the confirmation status for an already confirmed transaction
$user->resetConfirm($transaction); // or
$user->safeResetConfirm($transaction); // will return false in case of exceptions
```

### Payments
In case you need a market like wallet with payment capabilities we got you covered.

In your user model implement the `Customer` interface and use the `CanPay` trait as follows
```php
class Wallet extends Model implements Customer
{
    use CanPay;
    
    // ...
}
```

Then in any of your model or classes implement the `Product` interface and use the `HasWallet` trait as follows.
```php
class SampleFullProduct extends Model implements Product
{
    use HasWallet;
    
    /**
     * Check whether the provided customer has enough funds to buy the given quantity of the current product
     *
     * @param Customer $customer Product buyer
     * @param int $quantity Amount of product buying
     * @param bool $force Whether the buyer's balance can go below 0
     * @return bool
     */
    public function canBuy(Customer $customer, int $quantity = 1, bool $force = false): bool
    {
        return true;
    }

    /**
     * Defines how much the product costs
     * This value by default is not stored in any field of the record
     *
     * @param Customer $customer Product buyer, useful to personalize the price per user
     * @return string
     */
    public function getCostAttribute(Customer $customer): string
    {
        return "1000";
    }

    /**
     * Metadata attributes assigned to the product, this can be used to identify one or more products while
     * examining transactions & transfers
     *
     * @return array
     */
    public function getMetadataAttribute(): array
    {
        return [];
    }
}
```

You can interact with products via a simple API as in the following sample
```php
$user = User::first();
$product = new SampleFullProduct();

// Get the product for free
$user->payFree($product);

// Pay for the product
$user->pay($product); // or
$user->forcePay($product); // or
$user->safePay($product); // silence exceptions and eventually returns null

// Refund an already paid product
$user->refund($product); // or
$user->forceRefund($product); // silence exceptions and eventually returns null

// Get the last (non refunded) payment for a given product
$user->getPayment($product);

// Get the list of non refunded payments for a given product
$user->getAllPayments($product);

// Check whether the given product was bought at least once (and not refunded)
$user->paid($product);
```

#### Extensions
Products can be extended with some extra interfaces and traits, the available interfaces are:
* `Discountable` - Requires the usage of the `HasDiscount` trait and the implementation of the `getDiscountPercentageAttribute`
   method
* `Taxable` - Requires the usage of the `HasTax` trait and the implementation of the `getTaxPercentageAttribute` method
* `MinimalTaxable` - Requires the usage of the `HasTax` trait and the implementation of the `getMinimumTaxAttribute`
  method

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

If you discover any security related issues, please email [security@do-inc.co](mailto:security@do-inc.co) instead of using the issue tracker.

## Credits

This package is strongly inspired by [Bavix's laravel wallet](https://github.com/bavix/laravel-wallet).

- [Emanuele (ebalo) Balsamo](https://github.com/ebalo55)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
