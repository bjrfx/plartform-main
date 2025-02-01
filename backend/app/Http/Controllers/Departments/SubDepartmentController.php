<?php

namespace App\Http\Controllers\Departments;

use App\Http\Controllers\Controller;
use App\Http\Requests\Departments\SubDepartmentRequest;
use App\Http\Resources\Departments\SubDepartmentResource;
use App\Http\Resources\Departments\SubDepartmentsResource;
use App\Models\Departments\Department;
use App\Models\Departments\SubDepartment;
use App\Models\Merchants\Merchant;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;

class SubDepartmentController extends Controller
{
    public function index(string $merchantId, string $departmentId): AnonymousResourceCollection
    {
        $subDepartments = cache()->remember(
            "sub_departments_$departmentId",
            60 * 60,
            function () use ($departmentId) {
                return SubDepartment::query()
                    ->where('is_active', true)
                    ->where('department_id', $departmentId)
                    ->get();
            }
        );

        return SubDepartmentResource::collection($subDepartments);
    }

    public function edit(Merchant $merchant, Department $department): SubDepartmentsResource
    {
        $department->load('subDepartments');

        return SubDepartmentsResource::make($department, $merchant->getAttribute('name'));
    }

    public function save(SubDepartmentRequest $request, Merchant $merchant, Department $department = null): SubDepartmentsResource
    {
        $requestData = $request->validated();
        $label = Arr::get($requestData, 'label');
        $department->setAttribute('sub_department_label', $label);
        $department->save();

        $ids = [];
        $subs = Arr::get($requestData, 'subs');
        foreach ($subs as $sub) {
            $id = Arr::get($sub, 'id');
            $save = [
                'name' => Arr::get($sub, 'name'),
                'is_active' => Arr::get($sub, 'is_active'),
            ];
            if (is_null($id)) {
                $subDepartment = $department->subDepartments()->create($save);
            } else {
                $subDepartment = SubDepartment::query()->where('id', $id)->firstOrFail();
                $subDepartment->setAttribute('name', $save['name']);
                $subDepartment->setAttribute('is_active', $save['is_active']);
                $subDepartment->save();
            }
            $ids[] = $subDepartment->getKey();
        }

        $department->subDepartments()->whereNotIn('id', $ids)->delete();

        return $this->edit(merchant: $merchant, department: $department);
    }
}
