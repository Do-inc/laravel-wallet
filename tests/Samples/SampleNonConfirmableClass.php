<?php

namespace Doinc\Wallet\Tests\Samples;

use Doinc\Wallet\Interfaces\Confirmable;
use Doinc\Wallet\Traits\CanConfirm;

class SampleNonConfirmableClass implements Confirmable
{
    use CanConfirm;
}
