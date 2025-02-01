<?php

namespace App\Http\Resources\Departments;

use App\Models\Departments\Department;
use App\Models\Departments\SubDepartment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubDepartmentsResource extends JsonResource
{
    protected ?string $merchantName = null;

    public function __construct($resource, ?string $merchantName = null)
    {
        parent::__construct($resource);
        $this->merchantName = $merchantName;
    }

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Department $department */
        $department = $this->resource;

        $item = $department->getRelationValue('subDepartments');

        return [
            'merchant_name' => $this->merchantName,
            'name' => $department->getAttribute('name'),
            'label' => $department->getAttribute('sub_department_label'),
            'subs' => SubDepartmentResource::collection($item),
        ];
    }
}
