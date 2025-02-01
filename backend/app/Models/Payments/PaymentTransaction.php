<?php

namespace App\Models\Payments;

use App\Enums\Billings\PaymentMethodTypesEnums;
use App\Enums\Payments\PaymentTransactionChargeTypeEnums;
use App\Enums\Payments\PaymentTransactionStatusEnums;
use App\Observers\Payments\PaymentTransactionObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentTransaction extends Model
{
    use HasFactory, HasUuids;


    protected static function boot(): void
    {
        parent::boot();

        static::observe(PaymentTransactionObserver::class);
    }
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payment_transactions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'payment_department_id',
        'charge_type',
        'payment_method',
        'status',
        'status_at_tz',
        'status_code',
        'status_message',
        'amount',
        'reference_number',
        'batch_id',
        'settled_at_tz',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status_at_tz' => 'datetime',
            'settled_at_tz' => 'datetime',
            'amount' => 'decimal:2',
            'charge_type' => PaymentTransactionChargeTypeEnums::class,
            'status' => PaymentTransactionStatusEnums::class,
            'payment_method' => PaymentMethodTypesEnums::class,
        ];
    }


    public function paymentDepartment(): BelongsTo
    {
        return $this->belongsTo(PaymentDepartment::class);
    }

    public function paymentTransactionLogs(): HasMany
    {
        return $this->hasMany(PaymentTransactionLog::class);
    }
}
