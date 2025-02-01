<?php

/** @noinspection SpellCheckingInspection */

namespace App\Http\Controllers\Gateway;

use App\Enums\Gateway\GatewayTypeEnums;
use App\Helpers\General\CacheKeysHelper;
use App\Helpers\General\NumberFormatHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Gateway\PayaRequest;
use App\Http\Resources\Gateway\PayaResource;
use App\Models\Departments\Department;
use App\Models\Merchants\Merchant;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

class PayaController extends Controller
{
    /**
     * @note For internal processing that returns an array
     * @param Department $department
     * @return array
     */
    public function getMap(Department $department): array
    {
        $key = CacheKeysHelper::getPayaCacheKey($department->getKey());
        return cache()->remember(
            $key,
            3600,
            function () use ($department) {
                $department->load('paymentPayaGateway');

                return PayaResource::make($department)->resolve();
            });
    }

    public function edit(Merchant $merchant, Department $department): PayaResource
    {
        $department->load('paymentPayaGateway');

        return PayaResource::make($department, $merchant->getAttribute('name'));
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function save(PayaRequest $request, string $merchantId, Department $department): JsonResponse
    {
        $requestData = $request->validated();

        $department->load('paymentPayaGateway');

        $this->savePaya(requestData: $requestData, department: $department);

        $key = CacheKeysHelper::getPayaCacheKey($department->getKey());
        cache()->forget($key);

        return response()->json([
            'success' => true,
        ]);
    }

    private function savePaya(array $requestData, Department $department): void
    {
        $gateway = $department->getRelationValue('paymentPayaGateway');

        $feeAmount = Arr::get($requestData, 'fee_amount', 0);
        $feeAmount = NumberFormatHelper::make($feeAmount);

        $feePercentage = Arr::get($requestData, 'fee_percentage', 0);
        $feePercentage = NumberFormatHelper::make($feePercentage);

        $feeAmountLarge = Arr::get($requestData, 'fee_amount_large', 0);
        $feeAmountLarge = NumberFormatHelper::make($feeAmountLarge);

        $additionalData = [
            'web_terminal_id' => Arr::get($requestData, 'web_terminal_id'),
            'large_transaction_terminal_id' => Arr::get($requestData, 'large_transaction_terminal_id'),
            'ccd_void_terminal_id' => Arr::get($requestData, 'ccd_void_terminal_id'),
            'allow_guest_payment' => Arr::get($requestData, 'allow_guest_payment'),
            'fee_amount' => $feeAmount,
            'fee_percentage' => $feePercentage,
            'fee_amount_large' => $feeAmountLarge,
        ];

        $save = [
            'type' => GatewayTypeEnums::PAYA,
            'gateway_id' => Arr::get($requestData, 'gateway_id'),
            'is_active' => Arr::get($requestData, 'is_active') ?? false,
            'username' => Arr::get($requestData, 'username'),
            'password' => Arr::get($requestData, 'password'),
            'additional_data' => $additionalData,
        ];

        if (is_null($gateway)) {
            $department->gateways()->create($save);
        } else {
            $gateway->update($save);
        }
    }
}
