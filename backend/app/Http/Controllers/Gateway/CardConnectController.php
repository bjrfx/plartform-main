<?php

namespace App\Http\Controllers\Gateway;

use App\Enums\Gateway\GatewayTypeEnums;
use App\Helpers\General\CacheKeysHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Gateway\CardConnectRequest;
use App\Http\Resources\Gateway\CardConnectResource;
use App\Models\Departments\Department;
use App\Models\Merchants\Merchant;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

class CardConnectController extends Controller
{
    /**
     * @note For internal processing that returns an array
     * @param Department $department
     * @return array
     */
    public function getMap(Department $department): array
    {
        $key = CacheKeysHelper::getCardConnectCacheKey($department->getKey());
        return cache()->remember(
            $key,
            3600,
            function () use ($department) {
                $department->load(
                    'paymentCardConnectMerchantGateway',
                    'paymentCardConnectFeeGateway',
                );

                return CardConnectResource::make($department)->resolve();
            });
    }

    public function edit(Merchant $merchant, Department $department): CardConnectResource
    {
        $department->load(
            'paymentCardConnectMerchantGateway',
            'paymentCardConnectFeeGateway',
        );

        return CardConnectResource::make($department, $merchant->getAttribute('name'));
    }

    public function save(CardConnectRequest $request, string $merchantId, Department $department): JsonResponse
    {
        $requestData = $request->validated();

        $department->load(
            'paymentCardConnectMerchantGateway',
            'paymentCardConnectFeeGateway',
        );

        $this->saveMerchant(requestData: $requestData, department: $department);
        $this->saveFee(requestData: $requestData, department: $department);

        $key = CacheKeysHelper::getCardConnectCacheKey($department->getKey());
        cache()->forget($key);

        return response()->json([
            'success' => true,
        ]);
    }


    private function saveMerchant(array $requestData, Department $department): void
    {
        $gateway = $department->getRelationValue('paymentCardConnectMerchantGateway');
        $save = [
            'type' => GatewayTypeEnums::CARD_CONNECT_MERCHANT,
            'is_active' => Arr::get($requestData, 'is_active'),
            'username' => Arr::get($requestData, 'merchant_username'),
            'password' => Arr::get($requestData, 'merchant_password'),
            'external_identifier' => Arr::get($requestData, 'merchant_mid'),
            'gateway_id' => Arr::get($requestData, 'gateway_id'),
        ];
        if (is_null($gateway)) {
            $department->gateways()->create($save);
        } else {
            $gateway->update($save);
        }
    }

    private function saveFee(array $requestData, Department $department): void
    {
        $gateway = $department->getRelationValue('paymentCardConnectFeeGateway');

        $hasSameFee = Arr::get($requestData, 'has_same_fee');
        $save = [
            'type' => GatewayTypeEnums::CARD_CONNECT_FEE,
            'is_active' => Arr::get($requestData, 'is_active'),
            'username' => Arr::get($requestData, 'fee_username'),
            'password' => Arr::get($requestData, 'fee_password'),
            'external_identifier' => Arr::get($requestData, 'fee_mid'),
            'gateway_id' => Arr::get($requestData, 'gateway_id'),
            'additional_data' => [
                'has_same_fee' => $hasSameFee,
                'credit_card_min' => Arr::get($requestData, 'credit_card_min'),
                'credit_card_amount' => Arr::get($requestData, 'credit_card_amount'),
                'credit_card_percentage' => Arr::get($requestData, 'credit_card_percentage'),
                'debit_card_min' => $hasSameFee ? null : Arr::get($requestData, 'debit_card_min'),
                'debit_card_amount' => $hasSameFee ? null : Arr::get($requestData, 'debit_card_amount'),
                'debit_card_percentage' => $hasSameFee ? null : Arr::get($requestData, 'debit_card_percentage'),
            ],
        ];

        if (is_null($gateway)) {
            $department->gateways()->create($save);
        } else {
            $gateway->update($save);
        }
    }
}
