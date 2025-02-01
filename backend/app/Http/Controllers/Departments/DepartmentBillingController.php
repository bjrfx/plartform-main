<?php

namespace App\Http\Controllers\Departments;

use App\Http\Controllers\Controller;
use App\Http\Resources\Departments\DepartmentPublicResource;
use App\Http\Resources\Forms\HostedPaymentsFieldsResource;
use App\Models\Departments\Department;
use App\Services\Departments\DepartmentBillingService;
use App\Services\Forms\HostedPaymentsFieldsService;
use Illuminate\Validation\ValidationException;

class DepartmentBillingController extends Controller
{
    public function __construct(
        protected DepartmentBillingService    $service,
        protected HostedPaymentsFieldsService $hostedPaymentsFieldsService,
    )
    {
    }

    /**
     * @throws ValidationException
     */
    public function index($slug): DepartmentPublicResource
    {
        $department = $this->service->get(slug: $slug);

        //Need if based on something like department type -> HOSTED, EZSP,URL, TYLER

        return DepartmentPublicResource::make($department);
    }

    public function getHosted(Department $department): HostedPaymentsFieldsResource
    {
        $fields = [];
        $fields['default'] = $this->hostedPaymentsFieldsService->getDefaultFieldsDepartment(departmentId: $department->getKey());
        $fields['custom'] = $this->hostedPaymentsFieldsService->getCustomFieldsDepartment(departmentId: $department->getKey());

        if ($department->getAttribute('sub_department_label')) {
            $department->load(['subDepartments' => function ($query) {
                $query->where('is_active', 1);
            }]);
            $fields['sub_departments'] = $department->getAttribute('subDepartments');
        }

        return HostedPaymentsFieldsResource::make($fields);
    }

}
