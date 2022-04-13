<?php

namespace Doinc\Wallet\Models;

use Carbon\Carbon;
use Doinc\Wallet\BigMath;
use Doinc\Wallet\Enums\TransactionType;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;

/**
 * @property int|string $id
 * @property string|int $from_id
 * @property string $from_type
 * @property string|int $to_id
 * @property string $to_type
 * @property TransactionType $type
 * @property string $amount
 * @property bool $refunded
 * @property bool $confirmed
 * @property Carbon $confirmed_at
 * @property Collection $metadata
 * @property string $discount
 * @property string $fee
 * @property Model $from
 * @property Model $to
 */
class Transaction extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = [
        "from_id",
        "from_type",
        "to_id",
        "to_type",
        "type",
        "amount",
        "confirmed",
        "confirmed_at",
        "metadata",
        "discount",
        "fee",
    ];

    /**
     * @var array
     */
    protected $casts = [
        "type" => TransactionType::class,
        'confirmed' => 'bool',
        'refunded' => 'bool',
        'metadata' => AsCollection::class,
        'confirmed_at' => "datetime"
    ];

    public function from(): MorphTo
    {
        return $this->morphTo();
    }

    public function to(): MorphTo
    {
        return $this->morphTo();
    }

    public function discount(): Attribute {
        return Attribute::make(set: fn($value) => BigMath::add($value, 0));
    }

    public function fee(): Attribute {
        return Attribute::make(set: fn($value) => BigMath::add($value, 0));
    }

    public function amount(): Attribute {
        return Attribute::make(set: fn($value) => BigMath::add($value, 0));
    }
}
