<?php

namespace App\Services\Users;

use App\Enums\Users\UserRoleEnums;
use App\Helpers\General\CacheKeysHelper;
use App\Helpers\General\DomainHelper;
use App\Models\Departments\Department;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserService
{
    /**
     * @throws ValidationException
     */
    public function getUser(string $userId): User
    {
        $merchant = DomainHelper::getMerchant();
        $merchantId = $merchant?->getKey();
        $key = CacheKeysHelper::getUserCacheKey($userId);
        cache()->forget($key);
        /** @var User $user */
        $user = cache()->remember(
            $key,
            60 * 60,
            function () use ($userId, $merchantId) {
                return User::query()
                    ->with('merchant', 'departments')
                    ->when(!is_null($merchantId), function ($query) use ($merchantId) {
                        return $query->where('merchant_id', $merchantId);
                    })
                    ->findOrFail($userId);
            }
        );


        if (!Gate::allows('assignRole', [$user, $user->getAttribute('role')])) {
            throw ValidationException::withMessages([
                'assign_role' => "Unauthorized getUser based on auth role",
            ]);
        }

        return $user;
    }

    /**
     * @noinspection SpellCheckingInspection
     */
    /**
     * @throws ValidationException
     */
    public function save(array $requestData, ?User $user): User
    {
        $save = [
            'role' => Arr::get($requestData, 'role', UserRoleEnums::MEMBER),
            'first_name' => Arr::get($requestData, 'first_name'),
            'middle_name' => Arr::get($requestData, 'middle_name'),
            'last_name' => Arr::get($requestData, 'last_name'),
            'email' => Arr::get($requestData, 'email'),
            'phone_country_code' => Arr::get($requestData, 'phone_country_code'),
            'phone' => Arr::get($requestData, 'phone'),
            'street' => Arr::get($requestData, 'street'),
            'city' => Arr::get($requestData, 'city'),
            'state' => Arr::get($requestData, 'state'),
            'zip_code' => Arr::get($requestData, 'zip_code'),
            'is_ebilling_enabled' => Arr::get($requestData, 'is_ebilling_enabled', false),
            'merchant_id' => Arr::get($requestData, 'merchant_id'),
        ];

        if (!Gate::allows('assignRole', [$user, $save['role']])) {
            throw ValidationException::withMessages([
                'assign_role' => "Unauthorized save based on auth role",
            ]);
        }

        $password = Arr::get($requestData, 'password');
        if (!is_null($password)) {
            $save['password'] = Hash::make($password);
        }
        if (is_null($password) && is_null($user)) {
            $save['password'] = User::INTERNAL_CREATED_PASSWORD;
        }

        if (is_null($user)) {
            /** @var User $user */
            $user = User::query()->create($save);
        } else {
            $user->update($save);
        }

        $this->assignUserDepartmentsAccess(
            user: $user,
            requestData: $requestData
        );

        return $user;
    }

    private function assignUserDepartmentsAccess(User $user, array $requestData): void
    {
        $departmentIds = Arr::get($requestData, 'department_ids', []); // Array of department UUIDs

        if (count($departmentIds) > 0) {
            //Precaution in-case the user got moved to another merchant
            $departmentIds = Department::query()
                ->whereIn('id', $departmentIds)
                ->where('merchant_id', $user->getAttribute('merchant_id'))
                ->pluck('id')
                ->all();
        }

        $user->departments()->sync($departmentIds);

        //Since departments sync is after the user update
        //This will update the 'updated_at' timestamp and trigger the 'updated' event
        $user->touch();
    }
}
