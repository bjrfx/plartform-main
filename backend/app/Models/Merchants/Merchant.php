<?php

namespace App\Models\Merchants;

use App\Models\Departments\Department;
use App\Models\Payments\Payment;
use App\Models\User;
use App\Observers\Merchants\MerchantObserver;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Merchant extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected static function boot(): void
    {
        parent::boot();

        static::observe(MerchantObserver::class);
    }

    protected $table = 'merchants';
    protected $fillable = [
        'id',
        'name',
        'subdomain',
        'address',
        'city',
        'state',
        'zip',
        'phone',
        'logo',
        'fax',
        'time_zone',
        'is_enabled',
        'is_bulk_notifications_enabled',
        'is_payment_service_disabled',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'is_bulk_notifications_enabled' => 'boolean',
            'is_payment_service_disabled' => 'boolean',
        ];
    }

    protected function subdomain(): Attribute
    {
        return Attribute::make(
            set: fn(string $value) => $value ? Str::slug($value) : '',
        );
    }

    public function user(): HasMany
    {
        return $this->hasMany(User::class, 'merchant_id', 'id');
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class, 'merchant_id', 'id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'merchant_id', 'id');
    }
}
