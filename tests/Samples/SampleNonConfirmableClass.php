<?php

namespace Doinc\Wallet\Tests\Samples;

use Doinc\Wallet\Interfaces\Confirmable;
use Doinc\Wallet\Traits\CanConfirm;
use Doinc\Wallet\Traits\ParsesWallet;

class SampleNonConfirmableClass implements Confirmable
{
    use ParsesWallet;
    use CanConfirm;
}
