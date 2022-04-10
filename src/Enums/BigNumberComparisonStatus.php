<?php

namespace Doinc\Wallet\Enums;

enum BigNumberComparisonStatus: int
{
    case LOWER_THAN = -1;
    case EQUAL = 0;
    case HIGHER_THAN = 1;
}
