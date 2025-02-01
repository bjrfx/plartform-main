<?php
/** @noinspection SpellCheckingInspection */

namespace App\Http\Resources\Forms;

use App\Http\Resources\Departments\SubDepartmentResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class HostedPaymentsFieldsResource extends JsonResource
{
    protected ?string $merchantName = null;
    protected ?string $departmentName = null;

    public function __construct($resource, ?string $merchantName = null, ?string $departmentName = null)
    {
        parent::__construct($resource);
        $this->merchantName = $merchantName;
        $this->departmentName = $departmentName;
    }

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $fields = $this->resource;

        $subDepartments = Arr::get($fields, 'sub_departments');
        return [
            'merchant_name' => $this->merchantName,
            'name' => $this->departmentName,
            'default' => HostedPaymentsDefaultFieldsResource::collection($fields['default']),
            'custom' => HostedPaymentsCustomFieldsResource::collection($fields['custom']),
            'sub_departments' => $this->when(!is_null($subDepartments), function () use ($subDepartments) {
                return SubDepartmentResource::collection($subDepartments);
            }),
        ];
    }
}
