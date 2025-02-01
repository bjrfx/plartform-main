<?php

namespace App\Services\Departments;

use App\Helpers\General\CacheKeysHelper;
use App\Models\Departments\Department;
use App\Models\Merchants\Merchant;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class DepartmentService
{
    protected ?Merchant $merchant = null;

    public function __construct()
    {
        $this->merchant = context('merchant');
    }

    public function all(): Collection
    {
        $merchantId = $this->merchant->getKey();

        $key = CacheKeysHelper::getDepartment($merchantId);
        cache()->forget($key);

        $merchant = cache()->remember(
            $key,
            60 * 60,
            function () {
                return $this->merchant->load(['departments' => function (Builder $query) {
                    $query->scopes([
                        'isVisible'
                    ])
                        ->with('icon')
                        ->orderBy('display_order');
                }]);
            });

        return $merchant->getRelationValue('departments');
    }

    public function get(string $departmentId): Department
    {
        $key = CacheKeysHelper::getDepartment($departmentId);
        return cache()->remember(
            $key,
            60 * 60,
            function () use ($departmentId) {
                /** @var Department $merchant */
                return Department::query()
                    ->with('icon')
                    ->where('id', $departmentId)
                    ->where('merchant_id', $this->merchant->getKey())
                    ->firstOrFail();
            }
        );
    }

    public function list(Merchant $merchant): Merchant
    {
        $merchant->load(['departments' => function (Builder $query) {
            $query->with('icon')
                ->orderBy('display_order');
        }]);

        return $merchant;
    }

    public function show(Department $department): Department
    {
        $department->load('icon');

        return $department;
    }

    public function save(Merchant $merchant, array $requestData, ?Department $department = null): Department
    {
        // Check if we're updating or creating
        if (is_null($department)) {
            $department = $merchant->departments()->create($requestData);
        } else {
            $department->update($requestData);
        }

        return $department;
    }
}
