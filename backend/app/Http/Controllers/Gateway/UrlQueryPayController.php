<?php

namespace App\Http\Controllers\Gateway;

use App\Enums\Gateway\GatewayTypeEnums;
use App\Helpers\General\CacheKeysHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Gateway\UrlQueryPayRequest;
use App\Http\Resources\Gateway\UrlQueryPayResource;
use App\Models\Departments\Department;
use App\Models\Merchants\Merchant;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

/**
 * @todo
 * When a bill gets paid the data will be needed to be stored like it was an EZSP bill
 * and the payment should be ref. between the user to ezsp
 */
class UrlQueryPayController extends Controller
{
    /**
     * @note For internal processing that returns an array
     * @param Department $department
     * @return array
     */
    public function getMap(Department $department): array
    {
        $key = CacheKeysHelper::getUrlQueryPayKey($department->getKey());
        return cache()->remember(
            $key,
            3600,
            function () use ($department) {
                $department->load('urlQueryPayGateway');

                return UrlQueryPayResource::make($department)->resolve();
            });
    }

    public function edit(Merchant $merchant, Department $department): UrlQueryPayResource
    {
        $department->load('urlQueryPayGateway');

        return UrlQueryPayResource::make(
            $department,
            $merchant->getAttribute('name'),
            $merchant->getAttribute('subdomain')
        );
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function save(UrlQueryPayRequest $request, string $merchantId, Department $department): JsonResponse
    {
        $requestData = $request->validated();

        $department->load('ezSecurePayGateway');

        $this->saveGateway(requestData: $requestData, department: $department);

        $key = CacheKeysHelper::getUrlQueryPayKey($department->getKey());
        cache()->forget($key);

        return response()->json([
            'success' => true,
        ]);
    }

    private function saveGateway(array $requestData, Department $department): void
    {
        $gateway = $department->getRelationValue('ezSecurePayGateway');

        $additionalData = [
            'amount_due' => Arr::get($requestData, 'bill_amount'),//bill amount to be paid
            'bill_payer_id' => Arr::get($requestData, 'bill_payer_id'),//Client reference for report
            'product_code' => Arr::get($requestData, 'product_code'),//Client reference for report
            'bill_number' => Arr::get($requestData, 'bill_number'),//bill reference number
        ];

        /*
         * PAYERID: The reference ID from the payment request
         * https://dashatest.recoonline.io:/url/engineering?billPayorId={ABC12345678}&productCode={XXXX}&amountDue={XX.XX}&PAYERID={ABC12345678}
         *  'amountDue' => number_format($paymentOrder->base_total, 2, '.', ''),
                'PAYERID' => $cartData[0]['PAYERID'] ?? '',
                'ClientId' => $settings->sourceUrl->client_id,
                'ClientPassword' => $settings->sourceUrl->client_pw,
                'PaymentMethod' => $method === 'ach' ? 'EC' : 'CC',
                'PaymentAmount' => number_format($paymentOrder->base_total, 2, '.', ''),
                'ConvenienceFee' => number_format($paymentOrder->fee_amount, 2, '.', ''),
                'ConfirmationId' => $result['payment']['trn_id'] ?? '',
                'StatusCD' => 'PAYMENT',
                'PaymentEffectiveDate' => TimeZoneHelper::getDateTimeByTimeZone(date('Y-m-d H:i:s'), $md->merchant->time_zone, 'Y-m-d'),
         */

        $save = [
            'type' => GatewayTypeEnums::URL_QUERY_PAY,
            'custom_url' => Arr::get($requestData, 'callback_url'),
            'is_active' => Arr::get($requestData, 'is_active'),
            'username' => Arr::get($requestData, 'client_id'),
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
