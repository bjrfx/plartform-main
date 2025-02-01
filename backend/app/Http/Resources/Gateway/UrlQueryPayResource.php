<?php
/** @noinspection SpellCheckingInspection */

namespace App\Http\Resources\Gateway;

use App\Helpers\General\DomainHelper;
use App\Models\Departments\Department;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class UrlQueryPayResource extends JsonResource
{
    protected ?string $merchantName = null;
    protected ?string $subdomain = null;

    public function __construct($resource, ?string $merchantName = null, ?string $subdomain = null)
    {
        parent::__construct($resource);
        $this->merchantName = $merchantName;
        $this->subdomain = $subdomain;
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

        $item = $department->getRelationValue('ezSecurePayGateway');

        $additionalData = [];
        if (!is_null($item)) {
            $additionalData = $item->getAttribute('additional_data');
        }

        $data = [
            'subdomain' => $this->subdomain . '.' . DomainHelper::getDomain(),
            'merchant_name' => $this->merchantName,
            'name' => $department->getAttribute('name'),
            'is_active' => $item?->getAttribute('is_active'),
            'callback_url' => $item?->getAttribute('custom_url'),
            'client_id' => $item?->getAttribute('username'),
            'password' => $item?->getAttribute('password'),
            'bill_amount' => Arr::get($additionalData, 'bill_amount'),
            'bill_payer_id' => Arr::get($additionalData, 'bill_payer_id'),
            'product_code' => Arr::get($additionalData, 'product_code'),
            'bill_number' => Arr::get($additionalData, 'bill_number'),
        ];
        if (is_null($item)) {
            $data['bill_amount'] = 'amountDue';
            $data['bill_payer_id'] = 'billPayorId';
            $data['product_code'] = 'productCode';
            $data['bill_number'] = 'PAYERID';
        }

        return $data;
    }
}
