<?php

namespace App\Models\Payments;

use App\Enums\Payments\PaymentDepartmentStatusEnums;
use App\Models\Departments\Department;
use App\Observers\Payments\PaymentDepartmentObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentDepartment extends Model
{
    use HasFactory, HasUuids;

    protected static function boot(): void
    {
        parent::boot();

        static::observe(PaymentDepartmentObserver::class);
    }

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payment_departments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'payment_id',
        'department_id',
        'total_paid_amount',
        'total_bill_amount',
        'total_fee_amount',
        'base_fee_amount',
        'base_fee_percentage',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_paid_amount' => 'decimal:2',
            'total_bill_amount' => 'decimal:2',
            'total_fee_amount' => 'decimal:2',
            'base_fee_amount' => 'decimal:2',
            'base_fee_percentage' => 'decimal:2',
            'status' => PaymentDepartmentStatusEnums::class,
        ];
    }


    /**
     * Get the payment associated with this payment department.
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function paymentDepartmentBills(): HasMany
    {
        return $this->hasMany(PaymentDepartmentBill::class);
    }

    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }
}
