<?php

namespace Doinc\Wallet\Enums;

enum TransactionType: string
{
    case WITHDRAW = "withdraw";
    case DEPOSIT = "deposit";
    case TRANSFER = "transfer";
    case PAYMENT = "payment";
    case REFUND = "refund";
}
