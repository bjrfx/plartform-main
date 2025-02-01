<?php
/** @noinspection SpellCheckingInspection */

namespace App\Http\Resources\Gateway;

use App\Models\Departments\Department;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class DirectStatementResource extends JsonResource
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

        $item = $department->getRelationValue('paymentDirectStatementGateway');

        return [
            'merchant_name' => $this->merchantName,
            'name' => $department->getAttribute('name'),
            'is_active' => $item?->getAttribute('is_active'),
            'custom_url' => $item?->getAttribute('custom_url'),
            'token' => $item?->getAttribute('external_identifier'),
        ];
    }
}
