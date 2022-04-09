<?php
/*
* Copyright (c) 2022 - Do Group LLC - All Right Reserved.
* Unauthorized copying of this file, via any medium is strictly prohibited
* Proprietary and confidential
* Written by Emanuele (ebalo) Balsamo <emanuele.balsamo@do-inc.co>, 2022
*/

namespace Doinc\Wallet\Events;

use Doinc\Wallet\Models\Transaction;
use Doinc\Wallet\Models\Wallet;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionConfirmationReset
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
    * Create a new event instance.
    *
    * @return  void
    */
    public function __construct(
        public Wallet $wallet,
        public Transaction $transaction
    ) {
    }
}
