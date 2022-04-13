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
    ],
];
