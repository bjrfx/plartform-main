<?php

namespace App\Models\Gateway;

use App\Observers\Gateways\GatewayObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gateway extends Model
{
    use HasUuids, SoftDeletes;

    /**
     * Boot the model and register the observer.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::observe(GatewayObserver::class);
    }

    protected $fillable = [
        'name',
        'type',
        'base_url',
        'alternate_url',
    ];

    public function departmentCredentials(): HasMany
    {
        return $this->hasMany(DepartmentGateway::class);
    }
}
