<?php
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
     * | Define the decimal precision used while doing calculus
     * |
     */
    "decimals" => 2,

    /*
     * |--------------------------------------------------------------------------
     * | Decimal precision for fee value
     * |--------------------------------------------------------------------------
     * |
     * | Define the decimal precision used while applying fees
     * |
     */
    "fee_decimals" => 6,

    /*
     * |--------------------------------------------------------------------------
     * | Decimal precision for discount value
     * |--------------------------------------------------------------------------
     * |
     * | Define the decimal precision used while applying discounts
     * |
     */
    "discount_decimals" => 6,

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
    ],
];
