<?php

/** @noinspection SpellCheckingInspection */

namespace App\Models;

use App\Acl\RoleHierarchy;
use App\Enums\Users\UserRoleEnums;
use App\Helpers\General\PhoneFormatter;
use App\Models\Departments\Department;
use App\Models\Merchants\Merchant;
use App\Observers\Users\UserObserver;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Builder;

/**
 * @method static Builder|User visibleToAuthUser
 *
 * @method static Builder|User query
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasUuids;

    public const string INTERNAL_CREATED_PASSWORD = "INTERNAL_CREATED_PASSWORD";

    /**
     * Boot the model and register the observer.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Register the UserObserver
        static::observe(UserObserver::class);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     * @noinspection SpellCheckingInspection
     */
    protected $fillable = [
        'id', // UUID primary key
        'name', // Full name
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'phone_country_code',
        'phone',
        'password',
        'street',
        'city',
        'state',
        'zip_code',
        'role',
        'is_enabled',
        'email_verified_at_tz',
        'is_ebilling_enabled',
        'ebilling_opt_at_tz',
        'is_card_payment_only',
        'only_card_payment_updated_at_tz',
        'merchant_id',
        'profile_updated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_enabled' => 'boolean',
            'is_ebilling_enabled' => 'boolean',
            'is_card_payment_only' => 'boolean',
            'deleted_at' => 'datetime',
            'role' => UserRoleEnums::class,
            'email_verified_at_tz' => 'datetime',
            'ebilling_opt_at_tz' => 'datetime',
            'profile_updated_at' => 'datetime',
            'only_card_payment_updated_at_tz' => 'datetime',
        ];
    }

    /**
     * @noinspection PhpUnused
     */
    protected function formattedPhoneNumber(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value, array $attributes) => PhoneFormatter::format(
                phoneCode: $attributes['phone_country_code'],
                phoneNumber: $attributes['phone'],
            ),
        );
    }

    /**
     * Scope a query based on the auth user role.
     *
     * @noinspection PhpUnused
     */
    public function scopeVisibleToAuthUser($query): Builder
    {
        /** @var User $user */
        $user = auth()->user();

        $searchableRoles = RoleHierarchy::getAccessibleRolesFor(
            role: $user->getAttribute('role')->value
        );

        // Apply the roles to the query
        return $query->whereIn('role', $searchableRoles);
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class, 'merchant_id', 'id');
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'user_departments', 'user_id', 'department_id');
    }
}
