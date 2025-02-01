<?php

namespace App\Models\Gateway;

use App\Enums\Gateway\GatewayTypeEnums;
use App\Models\Departments\Department;
use App\Observers\Gateways\DepartmentGatewayObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DepartmentGateway extends Model
{
    use HasUuids, SoftDeletes;

    /**
     * Boot the model and register the observer.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::observe(DepartmentGatewayObserver::class);
    }

    protected $table = 'department_gateway';
    protected $fillable = [
        'department_id',
        'gateway_id',
        'type',
        'username',
        'password',
        'external_identifier', //Stores tokens or merchant IDs depending on the integration
        'additional_data',
        'custom_url',
        'is_active'
    ];

    protected function casts(): array
    {
        return [
            'additional_data' => 'array',
            'is_active' => 'boolean',
            'type' => GatewayTypeEnums::class,
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function gateway(): BelongsTo
    {
        return $this->belongsTo(Gateway::class);
    }
}
