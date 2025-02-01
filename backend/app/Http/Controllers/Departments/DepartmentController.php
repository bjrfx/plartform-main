<?php

namespace App\Http\Controllers\Departments;

use App\Http\Controllers\Controller;
use App\Http\Requests\Departments\DepartmentRequest;
use App\Http\Resources\Departments\DepartmentPublicResource;
use App\Http\Resources\Departments\DepartmentResource;
use App\Models\Departments\Department;
use App\Models\Icons\Icon;
use App\Models\Merchants\Merchant;
use App\Services\Departments\DepartmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DepartmentController extends Controller
{
    public function __construct(
        protected DepartmentService $departmentService
    )
    {
    }

    public function index(): AnonymousResourceCollection
    {
        $departments = $this->departmentService->all();

        return DepartmentPublicResource::collection($departments);
    }

    public function list(Merchant $merchant): AnonymousResourceCollection
    {
        $merchant = $this->departmentService->list(merchant: $merchant);

        return DepartmentResource::collection($merchant->getRelationValue('departments'));
    }

    public function edit(Merchant $merchant, Department $department): DepartmentResource
    {
        $department = $this->departmentService->show($department);

        $hasAssessment = $department->assessments()->where('is_enabled', true)->exists();
        $department->setAttribute('has_assessments', $hasAssessment);

        $icons = Icon::query()
            ->orderBy('name')
            ->get();

        return DepartmentResource::make($department, $merchant->getAttribute('name'))->additional(compact('icons'));
    }

    public function save(DepartmentRequest $request, Merchant $merchant, Department $department = null): DepartmentResource
    {
        $requestData = $request->validated();

        $department = $this->departmentService->save(merchant: $merchant, requestData: $requestData, department: $department);

        return DepartmentResource::make($department);
    }

    public function listForAssessment(Merchant $merchant, ?Department $department = null): JsonResponse
    {
        $merchant->load([
            'departments' => function ($query) use ($department) {
                $query->select('merchant_id', 'id', 'name')
                    ->whereNull('parent_id')
                    ->where('is_enabled', true)
                    ->when($department?->getAttribute('parent_id'), function ($query, $parentId) {
                        $query->orWhere('id', $parentId);
                    });
            }
        ]);

        return response()->json($merchant->getRelationValue('departments'));
    }
}
