<?php

namespace App\Models\Forms;

use App\Enums\Forms\HostedPaymentsTypeEnums;
use App\Models\Departments\Department;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

/**
 * @method static Builder|HostedPayments forDepartment(string $merchantId)
 * @method static Builder|HostedPayments query()
 */
class HostedPayments extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $table = 'hosted_payments_form_fields';

    protected $fillable = [
        'id',
        'department_id',
        'parent_id',
        'label',
        'type',
        'is_required',
        'display_order',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'type' => HostedPaymentsTypeEnums::class,
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(HostedPayments::class, 'parent_id');
    }

    public function overrides(): HasMany
    {
        return $this->hasMany(HostedPayments::class, 'parent_id');
    }

    public function scopeForDepartment($query, string $departmentId)
    {
        return $query->where(function ($q) use ($departmentId) {
            $q->where('department_id', $departmentId)
                ->orWhereNull('department_id');
        });
    }
}
