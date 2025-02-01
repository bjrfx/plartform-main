<?php

namespace App\Http\Resources\Departments;

use App\Models\Departments\SubDepartment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubDepartmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var SubDepartment $department */
        $item = $this->resource;

        return [
            'id' => $item?->getKey(),
            'name' => $item?->getAttribute('name'),
            'is_active' => $item?->getAttribute('is_active'),
        ];
    }
}
