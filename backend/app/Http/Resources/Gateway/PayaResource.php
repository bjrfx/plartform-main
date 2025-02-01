<?php
/** @noinspection SpellCheckingInspection */

namespace App\Http\Resources\Gateway;

use App\Helpers\General\NumberFormatHelper;
use App\Models\Departments\Department;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class PayaResource extends JsonResource
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

        $paya = $department->getRelationValue('paymentPayaGateway');

        $additionalData = [];
        if (!is_null($paya)) {
            $additionalData = $paya->getAttribute('additional_data');
        }

        return [
            'merchant_name' => $this->merchantName,
            'name' => $department->getAttribute('name'),
            'is_active' => $paya?->getAttribute('is_active'),
            'gateway_id' => $paya?->getAttribute('gateway_id'),
            'username' => $paya?->getAttribute('username'),
            'password' => $paya?->getAttribute('password'),
            'web_terminal_id' => Arr::get($additionalData, 'web_terminal_id'),
            'large_transaction_terminal_id' => Arr::get($additionalData, 'large_transaction_terminal_id'),
            'ccd_void_terminal_id' => Arr::get($additionalData, 'ccd_void_terminal_id'),
            'allow_guest_payment' => Arr::get($additionalData, 'allow_guest_payment'),
            //Convenience Fee
            'fee_amount' => NumberFormatHelper::make(Arr::get($additionalData, 'fee_amount')),
            'fee_percentage' => NumberFormatHelper::make(Arr::get($additionalData, 'fee_percentage')),
            'fee_amount_large' => NumberFormatHelper::make(Arr::get($additionalData, 'fee_amount_large')),
        ];
    }
}
