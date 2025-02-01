<?php

namespace App\Observers\Users;

use App\Helpers\General\CacheKeysHelper;
use App\Helpers\General\DomainHelper;
use App\Jobs\Users\SendAccountCreatedMail;
use App\Jobs\Users\SendActivationMail;
use App\Models\User;
use App\Services\Users\PasswordResetService;

class UserObserver
{
    public function updating(User $user): void
    {
        $this->assignUserFullName(user: $user, isUpdate: true);
        $this->assignMerchantUserChanges(user: $user);
    }

    public function updated(User $user): void
    {
        $key = CacheKeysHelper::getUserCacheKey($user->getKey());
        cache()->forget($key);
    }

    public function creating(User $user): void
    {
        $this->assignUserFullName(user: $user);
    }

    public function created(User $user): void
    {
        /** @var PasswordResetService $service */
        $service = app(PasswordResetService::class);
        $token = $service->createEntry(user: $user);
        if (auth()->guest()) {
            // Send the activation email with the token
            //dispatch(new SendActivationMail(user: $user, token: $token))->afterResponse();
        } else {
            // Send the account created for that user email with the token
            //dispatch(new SendAccountCreatedMail(user: $user, token: $token))->afterResponse();
        }
    }

    private function assignUserFullName(User $user, bool $isUpdate = false): void
    {
        if ($isUpdate && !$user->wasChanged(['first_name', 'middle_name', 'last_name'])) {
            return;
        }
        $name = collect([
            $user->getAttribute('first_name'),
            $user->getAttribute('middle_name'),
            $user->getAttribute('last_name')
        ])
            ->filter()
            ->implode(' ');

        $user->setAttribute('name', $name);
    }

    private function assignMerchantUserChanges(User $user): void
    {
        $merchant = DomainHelper::getMerchant();
        if (is_null($merchant)) {
            return;
        }
        $now = now($merchant->getAttribute('time_zone'));
        /** @noinspection SpellCheckingInspection */
        if ($user->wasChanged('is_ebilling_enabled')) {
            /** @noinspection SpellCheckingInspection */
            $user->setAttribute('ebilling_opt_at_tz', $now);
        }
        if ($user->wasChanged('is_card_payment_only')) {
            $user->setAttribute('only_card_payment_updated_at_tz', $now);
        }
        if ($user->wasChanged([
            'first_name',
            'middle_name',
            'last_name',
            'email',
            'phone',
            'password',
            'street',
            'city',
            'state',
            'zip_code',
        ])) {
            $user->setAttribute('profile_updated_at', $now);
        }
    }
}
