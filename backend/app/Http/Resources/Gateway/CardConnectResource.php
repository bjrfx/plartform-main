<?php

namespace App\Http\Resources\Gateway;

use App\Helpers\General\NumberFormatHelper;
use App\Models\Departments\Department;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class CardConnectResource extends JsonResource
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
     * @param \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Department $department */
        $department = $this->resource;

        $merchantGateway = $department->getRelationValue('paymentCardConnectMerchantGateway');
        $feeGateway = $department->getRelationValue('paymentCardConnectFeeGateway');

        $additionalData = [];
        if (!is_null($feeGateway)) {
            $additionalData = $feeGateway->getAttribute('additional_data');
        }

        return [
            'merchant_name' => $this->merchantName,
            'name' => $department->getAttribute('name'),
            'is_active' => $merchantGateway?->getAttribute('is_active'),
            'gateway_id' => $merchantGateway?->getAttribute('gateway_id'),
            //MID
            'merchant_username' => $merchantGateway?->getAttribute('username'),
            'merchant_password' => $merchantGateway?->getAttribute('password'),
            'merchant_mid' => $merchantGateway?->getAttribute('external_identifier'),
            //Fee
            'fee_username' => $feeGateway?->getAttribute('username'),
            'fee_password' => $feeGateway?->getAttribute('password'),
            'fee_mid' => $feeGateway?->getAttribute('external_identifier'),
            //Indicates if credit and debit have the same fee
            'has_same_fee' => Arr::get($additionalData, 'has_same_fee', false),
            //Convenience Fee
            'credit_card_min' => NumberFormatHelper::make(Arr::get($additionalData, 'credit_card_min')),
            'credit_card_amount' => NumberFormatHelper::make(Arr::get($additionalData, 'credit_card_amount')),
            'credit_card_percentage' => NumberFormatHelper::make(Arr::get($additionalData, 'credit_card_percentage')),
            'debit_card_min' => NumberFormatHelper::make(Arr::get($additionalData, 'debit_card_min')),
            'debit_card_amount' => NumberFormatHelper::make(Arr::get($additionalData, 'debit_card_amount')),
            'debit_card_percentage' => NumberFormatHelper::make(Arr::get($additionalData, 'debit_card_percentage')),
            //Gateway
            'api' => $merchantGateway->getRelationValue('gateway')?->getAttribute('base_url'),
            'iframe' => $merchantGateway->getRelationValue('gateway')?->getAttribute('alternate_url'),
        ];
    }
}
