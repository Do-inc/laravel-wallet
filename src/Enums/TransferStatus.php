<?php

namespace Doinc\Wallet\Enums;

enum TransferStatus: string
{
    case TRANSFER = "transfer";
    case PAID = "paid";
    case REFUND = "refund";
    case GIFT = "gift";
}
