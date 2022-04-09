<?php

namespace Doinc\Wallet\Models;

use Doinc\Wallet\Interfaces\Confirmable;
use Doinc\Wallet\Interfaces\Customer;
use Doinc\Wallet\Interfaces\Wallet as WalletInterface;
use Doinc\Wallet\Traits\CanConfirm;
use Doinc\Wallet\Traits\CanExchange;
use Doinc\Wallet\Traits\CanPay;
use Doinc\Wallet\Traits\HasGift;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $holder_type
 * @property int|string $holder_id
 * @property string $name
 * @property string $slug
 * @property null|array metadata
 * @property int $precision
 * @property WalletInterface $holder
 * @property string $credit
 * @property string $currency
 */
class Wallet extends Model implements Customer, WalletInterface, Confirmable
{
    use CanConfirm;
    use CanPay;
    use HasGift;

    /**
     * @var string[]
     */
    protected $fillable = [
        'holder_type',
        'holder_id',
        'name',
        'slug',
        'uuid',
        'description',
        'meta',
        'balance',
        'decimal_places',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'decimal_places' => 'int',
        'meta' => 'json',
    ];

    protected $attributes = [
        'balance' => 0,
        'decimal_places' => 2,
    ];

    public function getTable(): string
    {
        if (!$this->table) {
            $this->table = config('wallet.wallet.table', 'wallets');
        }

        return parent::getTable();
    }

    public function setNameAttribute(string $name): void
    {
        $this->attributes['name'] = $name;

        /**
         * Must be updated only if the model does not exist or the slug is empty.
         */
        if (!$this->exists && !array_key_exists('slug', $this->attributes)) {
            $this->attributes['slug'] = Str::slug($name);
        }
    }

    /**
     * Under ideal conditions, you will never need a method. Needed to deal with out-of-sync.
     *
     * @throws LockProviderNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function refreshBalance(): bool
    {
        return app(AtomicServiceInterface::class)->block($this, function () {
            $whatIs = $this->getBalanceAttribute();
            $balance = $this->getAvailableBalanceAttribute();
            if (app(MathServiceInterface::class)->compare($whatIs, $balance) === 0) {
                return true;
            }

            return app(RegulatorServiceInterface::class)->sync($this, $balance);
        });
    }

    public function getOriginalBalanceAttribute(): string
    {
        return (string)$this->getRawOriginal('balance', 0);
    }

    /**
     * @return float|int|string
     */
    public function getAvailableBalanceAttribute()
    {
        return $this->walletTransactions()
            ->where('confirmed', true)
            ->sum('amount');
    }

    public function holder(): MorphTo
    {
        return $this->morphTo();
    }

    public function getCreditAttribute(): string
    {
        return (string)($this->meta['credit'] ?? '0');
    }

    public function getCurrencyAttribute(): string
    {
        return $this->meta['currency'] ?? Str::upper($this->slug);
    }

    protected function initializeMorphOneWallet(): void
    {
        $this->uuid = app(UuidFactoryServiceInterface::class)->uuid4();
    }
}
