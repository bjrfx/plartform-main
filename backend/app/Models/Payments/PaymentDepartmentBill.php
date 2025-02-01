<?php

namespace App\Models\Payments;

use App\Models\Departments\SubDepartment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentDepartmentBill extends Model
{
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payment_department_bills';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'payment_department_id',
        'sub_department_id',
        'bill_reference',
        'amount',
        'bill_payload',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'bill_payload' => 'array', // Automatically decode JSON into an array
            'amount' => 'decimal:2', // Cast amount to decimal with 2 decimal places
        ];
    }

    /**
     * Get the payment department associated with this bill.
     */
    public function paymentDepartment(): BelongsTo
    {
        return $this->belongsTo(PaymentDepartment::class, 'payment_department_id');
    }

    /**
     * Get the sub-department associated with this bill (if applicable).
     */
    public function subDepartment(): BelongsTo
    {
        return $this->belongsTo(SubDepartment::class, 'sub_department_id');
    }
}
