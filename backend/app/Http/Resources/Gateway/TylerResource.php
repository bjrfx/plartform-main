<?php
/** @noinspection SpellCheckingInspection */

namespace App\Http\Resources\Gateway;

use App\Models\Departments\Department;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class TylerResource extends JsonResource
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

        $item = $department->getRelationValue('tylerGateway');

        $additionalData = [];
        if (!is_null($item)) {
            $additionalData = $item->getAttribute('additional_data');
        }

        return [
            'merchant_name' => $this->merchantName,
            'name' => $department->getAttribute('name'),
            'is_active' => $item?->getAttribute('is_active'),
            'custom_url' => $item?->getAttribute('custom_url'),
            'username' => $item?->getAttribute('username'),
            'password' => $item?->getAttribute('password'),
            'jur' => Arr::get($additionalData, 'jur'),
            'flag_of_current_due' => Arr::get($additionalData, 'flag_of_current_due'),
            'cycle_dues' => Arr::get($additionalData, 'cycle_dues', []),
            'restriction' => Arr::get($additionalData, 'restriction', []),
        ];
    }
}
