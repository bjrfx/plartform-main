<?php

namespace App\Http\Resources\Departments;

use App\Models\Departments\Department;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class DepartmentPublicResource extends JsonResource
{
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

        return [
            'id' => $department->getKey(),
            'name' => $department->getAttribute('name'),
            'slug' => $department->getAttribute('slug'),
            'sub_department_label' => $department->getAttribute('sub_department_label'),
            'icon' => $this->when($department->relationLoaded('icon'), $department->getRelationValue('icon')?->getAttribute('svg_code')),
            'is_locked' => Auth::guard('sanctum')->guest() && !$department->getAttribute('is_public'),
            'type' => 'HOSTED',
        ];
    }
}
