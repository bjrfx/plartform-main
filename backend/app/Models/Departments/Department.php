<?php

namespace App\Models\Departments;

use App\Enums\Gateway\GatewayTypeEnums;
use App\Models\Forms\HostedPayments;
use App\Models\Gateway\DepartmentGateway;
use App\Models\Icons\Icon;
use App\Models\Merchants\Merchant;
use App\Models\Payments\PaymentDepartment;
use App\Models\User;
use App\Observers\Departments\DepartmentObserver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Department Model
 *
 * @method static Builder|Department isVisible()
 * @method static Builder|Department query()
 *
 * @mixin Builder
 */
class Department extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    /**
     * Boot the model and register the observer.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::observe(DepartmentObserver::class);
    }

    protected $table = 'departments';
    protected $fillable = [
        'id',
        'merchant_id',
        'name',
        'email', //For nightly transaction report
        'slug', //slug is a part of the URL that identifies with that department
        'icon_id',
        'logo',
        'person_name',
        'is_enabled', //Toggle to enable or disable the department
        'is_visible', //Toggle to show or hide the department from payers
        'is_public', //Toggle to if a guest can access the department
        'display_order',
        'sub_department_label',
        'parent_id', //Define is as Assessment attached to a department
        'description', //Assessment description
        'amount', //Assessment amount
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
            'is_visible' => 'boolean',
            'is_public' => 'boolean',
            'amount' => 'decimal:2',
        ];
    }

    protected function slug(): Attribute
    {
        return Attribute::make(
            set: fn(string $value) => $value ? Str::slug($value) : '',
        );
    }

    /**
     * Scope a query to only include public departments.
     */
    public function scopeIsVisible(Builder $query): Builder
    {
        return $query->where('is_visible', 1)
            ->whereNull('parent_id');
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function subDepartments(): HasMany
    {
        return $this->hasMany(SubDepartment::class)
            ->orderBy('name');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_departments', 'department_id', 'user_id');
    }

    public function icon(): BelongsTo
    {
        return $this->belongsTo(Icon::class);
    }

    public function assessmentPaidTo(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Department::class, 'parent_id');
    }

    public function gateways(): HasMany
    {
        return $this->hasMany(DepartmentGateway::class);
    }


    public function paymentCardConnectMerchantGateway(): HasOne
    {
        return $this->hasOne(DepartmentGateway::class)
            ->where('type', GatewayTypeEnums::CARD_CONNECT_MERCHANT->value)
            ->with('gateway');
    }

    public function paymentCardConnectFeeGateway(): HasOne
    {
        return $this->hasOne(DepartmentGateway::class)
            ->where('type', GatewayTypeEnums::CARD_CONNECT_FEE->value);
    }

    /** @noinspection SpellCheckingInspection */
    public function paymentPayaGateway(): HasOne
    {
        return $this->hasOne(DepartmentGateway::class)
            ->where('type', GatewayTypeEnums::PAYA->value);
    }

    public function paymentDirectStatementGateway(): HasOne
    {
        return $this->hasOne(DepartmentGateway::class)
            ->where('type', GatewayTypeEnums::DIRECT_STATEMENT->value);
    }

    public function tylerGateway(): HasOne
    {
        return $this->hasOne(DepartmentGateway::class)
            ->where('type', GatewayTypeEnums::TYLER->value);
    }

    public function urlQueryPayGateway(): HasOne
    {
        return $this->hasOne(DepartmentGateway::class)
            ->where('type', GatewayTypeEnums::URL_QUERY_PAY->value);
    }

    public function hostedPaymentsFormFields(): HasMany
    {
        return $this->hasMany(HostedPayments::class);
    }

    public function paymentDepartments(): HasMany
    {
        return $this->hasMany(PaymentDepartment::class);
    }
}
