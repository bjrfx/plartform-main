<?php

namespace App\Models\Payments;


use App\Models\Merchants\Merchant;
use App\Models\User;
use App\Observers\Payments\PaymentDepartmentObserver;
use App\Observers\Payments\PaymentObserver;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasFactory, HasUuids;

    protected static function boot(): void
    {
        parent::boot();

        static::observe(PaymentObserver::class);
    }

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'merchant_id',
        'payment_method',
        'created_at_tz',
        'user_id',
        'card_owner',//When the payment method is credit card this will be the name on the card
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'phone',
        'address',
        'address_2',
        'city',
        'state',
        'zip_code',
        'ip_address',
        'user_agent',
        'payment_reference',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at_tz' => 'datetime',
        ];
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value, array $attributes) => trim(implode(' ', array_filter([
                $attributes['first_name'] . ' ' .
                (is_null($attributes['middle_name']) ? '' : $attributes['middle_name'] . ' ') .
                $attributes['last_name']
            ])))
        );
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paymentDepartments(): HasMany
    {
        return $this->hasMany(PaymentDepartment::class);
    }
}
