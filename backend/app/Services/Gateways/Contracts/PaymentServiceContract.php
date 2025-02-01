<?php

namespace App\Services\Gateways\Contracts;

use App\Adapters\Transactions\Dtos\ResponseDto;
use App\Enums\Payments\PaymentTransactionChargeTypeEnums;
use App\Enums\Payments\PaymentTransactionStatusEnums;
use App\Helpers\General\CacheKeysHelper;
use App\Helpers\General\DomainHelper;
use App\Models\Gateway\DepartmentGateway;
use App\Models\Gateway\Gateway;
use App\Models\Payments\Payment;
use App\Models\Payments\PaymentTransaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

abstract class PaymentServiceContract
{
    public ?Collection $departmentGateways;

    abstract public function index(): Collection;

    abstract public function save(array $requestData, ?Gateway $gateway = null): Gateway;

    abstract public function checkout(Payment $payment, string $merchantName, mixed $paymentMetadata = null): array;

    public function saveGateway(array $requestData, ?Gateway $gateway = null): Gateway
    {
        $save = [
            'name' => Arr::get($requestData, 'name'),
            'type' => Arr::get($requestData, 'type'),
            'base_url' => Arr::get($requestData, 'base_url'),
            'alternate_url' => Arr::get($requestData, 'alternate_url'),
        ];

        if (is_null($gateway)) {
            /** @var Gateway $gateway */
            $gateway = Gateway::query()->create($save);
        } else {
            $gateway->update($save);
        }
        return $gateway;
    }

    public function setTransactionProcessingStatus(PaymentTransaction $paymentTransaction): void
    {
        $paymentTransaction->setAttribute('status', PaymentTransactionStatusEnums::PROCESSING);
        $paymentTransaction->save();
    }

    public function setTransactionResponseStatus(PaymentTransaction $paymentTransaction, ResponseDto $response): void
    {
        $status = $response->success ? PaymentTransactionStatusEnums::SUCCESSFUL : PaymentTransactionStatusEnums::FAILED;
        $paymentTransaction->setAttribute('status', $status);
        $paymentTransaction->setAttribute('status_code', $response->status_code);
        $paymentTransaction->setAttribute('status_message', $response->status_message);
        $paymentTransaction->setAttribute('reference_number', $response->reference_number);
        $paymentTransaction->setAttribute('batch_id', $response->batch_id);
        $paymentTransaction->save();

        $this->setTransactionFeeNotExecuted(
            paymentTransaction: $paymentTransaction,
            status: $status
        );
    }

    public function setTransactionProcessingReversalStatus(PaymentTransaction $paymentTransaction): void
    {
        if ($paymentTransaction->getAttribute('charge_type') === PaymentTransactionChargeTypeEnums::BILL) {
            //Can apply only to the merchant - bill
            $paymentTransaction->setAttribute('status', PaymentTransactionStatusEnums::REVERSAL);
            $paymentTransaction->save();
        }
    }


    protected function getDepartmentsGateways(): void
    {
        $merchant = DomainHelper::getMerchant();
        $merchantId = $merchant->getKey();

        $key = CacheKeysHelper::getPaymentMerchantCacheKey($merchantId);

        $this->departmentGateways = cache()->remember(
            $key,
            3600,
            function () use ($merchantId) {
                return DepartmentGateway::query()
                    ->where('is_active', 1)
                    ->whereNotNull('gateway_id')
                    ->with('gateway')
                    ->whereHas('department', function (Builder $query) use ($merchantId) {
                        $query->where('merchant_id', $merchantId);
                    })
                    ->get();
            });
    }

    private function setTransactionFeeNotExecuted(PaymentTransaction $paymentTransaction, PaymentTransactionStatusEnums $status): void
    {
        if (
            $paymentTransaction->getAttribute('charge_type') === PaymentTransactionChargeTypeEnums::BILL
            &&
            $status === PaymentTransactionStatusEnums::FAILED
        ) {
            //if bill fail - flag the fee
            /** @var PaymentTransaction $paymentTransactionFee */
            $paymentTransactionFee = PaymentTransaction::query()->where('payment_department_id', $paymentTransaction->getAttribute('payment_department_id'))
                ->where('charge_type', PaymentTransactionChargeTypeEnums::FEE)
                ->first();
            if (!is_null($paymentTransactionFee)) {
                $paymentTransactionFee->setAttribute('status', PaymentTransactionStatusEnums::NOT_EXECUTED);
                $paymentTransaction->save();
            }
        }
    }
}
