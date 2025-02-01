<?php

namespace App\Http\Resources\Departments;

use App\Models\Departments\Department;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
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

        $logo = $department->getAttribute('logo');
        return [
            'merchant_name' => $this->merchantName,
            'id' => $department->getKey(),
            'name' => $department->getAttribute('name'),
            'email' => $department->getAttribute('email'),
            'slug' => $department->getAttribute('slug'),
            'logo' => $logo,
            'icon_id' => $department->getAttribute('icon_id'),
            'display_order' => $department->getAttribute('display_order'),
            'is_enabled' => $department->getAttribute('is_enabled'),
            'is_visible' => $department->getAttribute('is_visible'),
            'is_public' => $department->getAttribute('is_public'),
            'parent_id' => $department->getAttribute('parent_id'),
            'description' => $department->getAttribute('description'),
            'amount' => $department->getAttribute('amount'),
            'icon' => $this->when($department->relationLoaded('icon'), $department->getRelationValue('icon')?->getAttribute('svg_code')),
            'has_assessments' => $this->when($department->hasAttribute('has_assessments'), $department->getAttribute('has_assessments')),
        ];
    }
}
