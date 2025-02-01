<?php

namespace App\Observers\Departments;

use App\Models\Departments\SubDepartment;

class SubDepartmentObserver
{
    public function saved(SubDepartment $subDepartment): void
    {
        cache()->forget("sub_departments_{$subDepartment->getAttribute('department_id')}");
    }
}
