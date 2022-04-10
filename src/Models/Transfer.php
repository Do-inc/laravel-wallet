<?php

namespace Doinc\Wallet\Models;

use Doinc\Wallet\Enums\TransferStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class Transfer.
 *
 * @property TransferStatus $status
 * @property TransferStatus $status_last
 * @property string $discount
 * @property int $deposit_id
 * @property int $withdraw_id
 * @property string $from_type
 * @property int|string $from_id
 * @property string $to_type
 * @property int|string $to_id
 * @property string $fee
 * @property Transaction $deposit
 * @property Transaction $withdraw
 */
class Transfer extends Model
{
    /**
     * @var string[]
     */
    protected $fillable = [
        'status',
        'discount',
        'deposit_id',
        'withdraw_id',
        'from_type',
        'from_id',
        'to_type',
        'to_id',
        'fee',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'status' => TransferStatus::class,
        'status_last' => TransferStatus::class,
    ];

    public function from(): MorphTo
    {
        return $this->morphTo();
    }

    public function to(): MorphTo
    {
        return $this->morphTo();
    }

    public function deposit(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'deposit_id');
    }

    public function withdraw(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'withdraw_id');
    }
}
