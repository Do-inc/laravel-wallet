<?php

use Doinc\Wallet\Models\Wallet;
use Illuminate\Foundation\Auth\User;

it('can get wallet holder', function () {
    /** @var Wallet $sender */
    $sender = Wallet::factory()->create();
    /** @var User $holder */
    $holder = $sender->holder;

    expect($holder)->not()->toBeNull();
});
