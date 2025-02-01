<?php

namespace App\Observers\Departments;

use App\Helpers\General\CacheKeysHelper;
use App\Models\Departments\Department;

class DepartmentObserver
{
    public function creating(Department $department): void
    {
        $merchantId = $department->getAttribute('merchant_id');
        $maxOrder = Department::query()
            ->where('merchant_id', $merchantId)
            ->max('display_order');
        $maxOrder = is_null($maxOrder) ? 0 : $maxOrder;
        $department->setAttribute('display_order', $maxOrder + 1);
    }

    public function updated(Department $department): void
    {
        $key = CacheKeysHelper::getDepartment($department->getKey());
        cache()->forget($key);

        $key = CacheKeysHelper::getDepartments($department->getAttribute('merchant_id'));
        cache()->forget($key);
    }
}
