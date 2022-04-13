<?php

namespace Doinc\Wallet\Models;

use Doinc\Wallet\BigMath;
use Doinc\Wallet\Interfaces\Confirmable;
use Doinc\Wallet\Interfaces\Customer;
use Doinc\Wallet\Interfaces\Wallet as IWallet;
use Doinc\Wallet\Traits\CanConfirm;
use Doinc\Wallet\Traits\CanPay;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;

/**
 * @property int|string $id
 * @property string $holder_type
 * @property int|string $holder_id
 * @property string $name
 * @property Collection metadata
 * @property int $precision
 * @property string $balance
 * @property IWallet $holder
 */
class Wallet extends Model implements Customer, Confirmable
{
    use CanConfirm, CanPay, HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = [
        'holder_type',
        'holder_id',
        'name',
        'metadata',
        'balance',
        'precision',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'decimal_places' => 'int',
        'metadata' => AsCollection::class,
    ];

    public function holder(): MorphTo
    {
        return $this->morphTo();
    }

    public function balance(): Attribute {
        return Attribute::make(set: fn($value) => BigMath::add($value, 0));
    }
}
