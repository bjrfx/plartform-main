<?php

namespace App\Services\Gateways;

use App\Adapters\Transactions\Dtos\ResponseDto;
use App\Adapters\Transactions\Services\TransactionService;
use App\Enums\Billings\PaymentMethodTypesEnums;
use App\Enums\Gateway\GatewayTypeEnums;
use App\Enums\Payments\PaymentTransactionChargeTypeEnums;
use App\Enums\Transaction\TransactionProvidersEnums;
use App\Helpers\General\CacheKeysHelper;
use App\Models\Departments\Department;
use App\Models\Gateway\DepartmentGateway;
use App\Models\Gateway\Gateway;
use App\Models\Payments\Payment;
use App\Models\Payments\PaymentDepartment;
use App\Models\Payments\PaymentDepartmentBill;
use App\Services\Departments\DepartmentService;
use App\Services\Gateways\Contracts\PaymentServiceContract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class CardConnectService extends PaymentServiceContract
{
    public function __construct(
        protected TransactionService $transactionService,
        protected DepartmentService  $departmentService
    )
    {
    }

    public function index(): Collection
    {
        return Gateway::query()
            ->where('type', GatewayTypeEnums::CARD_CONNECT_MERCHANT)
            ->get();
    }

    public function save(array $requestData, ?Gateway $gateway = null): Gateway
    {
        $requestData['type'] = GatewayTypeEnums::CARD_CONNECT_MERCHANT;

        $gateway = $this->saveGateway(requestData: $requestData, gateway: $gateway);

        $departmentCredentials = $gateway->getRelationValue('departmentCredentials');

        foreach ($departmentCredentials as $department) {
            $key = CacheKeysHelper::getCardConnectCacheKey($department->getKey());
            cache()->forget($key);

            $merchantId = $department->getRelationValue('department')->getAttribute('merchant_id');
            $key = CacheKeysHelper::getPaymentMerchantCacheKey($merchantId);
            cache()->forget($key);
        }


        return $gateway;
    }

    /**
     * @throws ValidationException
     */
    public function fees(array $requestData): array
    {
        $token = Arr::get($requestData, 'token.token');

        $items = $this->getDepartmentsFeesGateway(requestData: $requestData);

        $data = [
            'type' => null,
            'fees' => [],
        ];
        if ($items->count() > 0) {
            $type = $this->getType(
                item: $items->first(),
                token: $token,
            );

            $data['type'] = $type;
            $data['fees'] = $this->calculateDepartmentsFee(items: $items, type: $type, requestData: $requestData);
        }
        return $data;
    }


    /**
     * @throws ValidationException
     */
    public function iFrameSrc(array $requestData)//: Collection
    {
        $this->getDepartmentsGateways();

        $departmentIds = Arr::get($requestData, 'department_ids');
        $departmentIds = array_unique($departmentIds);

        $items = $this->departmentGateways
            ->where('type', GatewayTypeEnums::CARD_CONNECT_MERCHANT)
            ->whereIn('department_id', $departmentIds);

        $this->validateIframeUsage(items: $items, departments: $requestData['department_ids']);

        return $items;
    }

    /**
     * @link https://developer.fiserv.com/product/CardPointe/api/?type=post&path=/cardconnect/rest/auth&branch=main&version=1.0.0
     * @throws ValidationException
     */
    public function checkout(Payment $payment, string $merchantName, mixed $paymentMetadata = null): array
    {
        $this->getDepartmentsGateways();

        $transactionDetails = $this->assignTransactionDetails(
            payment: $payment,
            merchantName: $merchantName,
            token: $paymentMetadata
        );

        $transactionDepartmentsStatus = [];

        $paymentDepartments = $payment->getRelationValue('paymentDepartments');
        foreach ($paymentDepartments as $paymentDepartment) {
            $departmentId = $paymentDepartment->getAttribute('department_id');

            $transactionMerchant = $this->checkoutMerchant(
                paymentDepartment: $paymentDepartment,
                transactionDetails: $transactionDetails,
            );

            //The "expiry" is part of the first transaction response and expected to be part of following transactions
            $transactionDetails['expiry'] = $transactionMerchant->expiry;

            $transactionMerchant->department_id = $departmentId;
            $transactionDepartmentsStatus[$departmentId] = $transactionMerchant;

            if ($transactionMerchant->success) {
                //execute fees
                $transactionFee = $this->checkoutFee(
                    paymentDepartment: $paymentDepartment,
                    transactionDetails: $transactionDetails,
                );

                if (!$transactionFee->success) {
                    $transactionMerchantVoid = $this->checkoutMerchantVoid(
                        paymentDepartment: $paymentDepartment,
                        transactionMerchant: $transactionMerchant
                    );

                    //adjust the status based on the fee rollback
                    $transactionDepartmentsStatus[$departmentId] = $transactionFee;
                }
            }
        }

        return $transactionDepartmentsStatus;
    }

    private function assignTransactionDetails(Payment $payment, string $merchantName, string $token): array
    {
        return [
            'name' => $payment->getAttribute('full_name'),
            'address' => $payment->getAttribute('address'),
            'address2' => $payment->getAttribute('address2'),
            'city' => $payment->getAttribute('city'),
            'region' => $payment->getAttribute('state'),
            'postal' => $payment->getAttribute('zip_code'),
            'phone' => $payment->getAttribute('phone'),
            'email' => $payment->getAttribute('email'),
            'orderid' => $payment->getAttribute('payment_reference'),//unique order ID for the transaction
            'account' => $token,//CardSecure Token
            'userfields' => [//additional data in the authorization request for future retrieval
                'merchant' => $merchantName,
                'user_id' => $payment->getAttribute('user_id'),
            ],
            'items' => [],
        ];
    }

    private function calculateDepartmentsFee(Collection $items, PaymentMethodTypesEnums $type, array $requestData): array
    {
        return $items->map(function (DepartmentGateway $item) use ($type, $requestData) {
            return [
                'department_id' => $item->getAttribute('department_id'),
                'amount' => $this->calculateDepartmentFee(
                    item: $item,
                    type: $type,
                    requestData: $requestData
                ),
            ];
        })->values()->all();
    }

    private function calculateDepartmentFee(DepartmentGateway $item, PaymentMethodTypesEnums $type, array $requestData): string
    {
        $departmentId = $item->getKey();
        $carts = Arr::get($requestData, 'cart');
        $total = collect($carts)->where('department_id', $departmentId)->sum('amount');
        $total = round($total, 2);

        $additionalData = $item->getAttribute('additional_data');

        $hasSameFee = Arr::get($additionalData, 'has_same_fee', false);

        if ($type === PaymentMethodTypesEnums::CREDIT || $hasSameFee) {
            return $this->calculateDepartmentCreditFee(additionalData: $additionalData, total: $total);
        }

        return $this->calculateDepartmentDebitFee(additionalData: $additionalData, total: $total);
    }

    private function calculateDepartmentCreditFee(array $additionalData, float $total): float
    {
        $feeMin = Arr::get($additionalData, 'credit_card_min', 0);
        if ((float)$feeMin > $total) {
            return $feeMin;
        }

        $fee = Arr::get($additionalData, 'credit_card_amount', 0);
        $feePercentage = (float)Arr::get($additionalData, 'credit_card_percentage', 0);
        if ($feePercentage > 0) {
            $fee += ($total * $feePercentage / 100);
        }

        return $fee;
    }

    private function calculateDepartmentDebitFee(array $additionalData, float $total): float
    {
        $feeMin = Arr::get($additionalData, 'debit_card_min', 0);
        if ((float)$feeMin > $total) {
            return $feeMin;
        }

        $fee = Arr::get($additionalData, 'debit_card_amount', 0);
        $feePercentage = (float)Arr::get($additionalData, 'debit_card_percentage', 0);
        if ($feePercentage > 0) {
            $fee += ($total * $feePercentage / 100);
        }

        return $fee;
    }

    private function getDepartmentsFeesGateway(array $requestData): Collection
    {
        $this->getDepartmentsGateways();

        $departmentIds = Arr::pluck($requestData['cart'], 'department_id');
        $departmentIds = array_unique($departmentIds);

        return $this->departmentGateways
            ->where('type', GatewayTypeEnums::CARD_CONNECT_FEE)
            ->whereIn('department_id', $departmentIds);
    }

    /**
     * @throws ValidationException
     */
    private function getType(DepartmentGateway $item, string $token): PaymentMethodTypesEnums
    {
        return $this->transactionService->make(
            providerEnum: TransactionProvidersEnums::CARD_CONNECT_WEB,
            departmentGateway: $item
        )->getType(token: $token);
    }

    /**
     * @throws ValidationException
     */
    private function validateIframeUsage(Collection $items, array $departments): void
    {
        // Check if the collection is empty
        if ($items->isEmpty()) {
            throw ValidationException::withMessages([
                'iFrameSrc' => "No departments are available for card payment processing. Please choose another payment method."
            ]);
        }
        if (count($departments) > $items->count()) {
            $departmentsIds = $items->pluck('department_id')->diff($departments)->all();
            $departments = Department::query()
                ->whereIn('id', $departmentsIds)
                ->pluck('name')
                ->join(', ');
            throw ValidationException::withMessages([
                'iFrameSrc' => "The following groups of departments do not support card payment: $departments. Please pay for each separately or choose another payment method."
            ]);
        }
        // Extract unique alternate URLs
        $uniqueAlternateUrls = $items->pluck('gateway.alternate_url')->filter()->unique();

        if ($uniqueAlternateUrls->count() > 1) {
            $departments = $uniqueAlternateUrls->map(function ($uniqueAlternateUrl, $index) use ($items) {
                // Get the department IDs for the current alternate URL
                $departmentsIds = $items->where('gateway.alternate_url', $uniqueAlternateUrl)->pluck('department_id');

                // Get department names and join them
                $departmentNames = Department::query()
                    ->whereIn('id', $departmentsIds)
                    ->pluck('name')
                    ->join(', ');

                return "Group " . ($index + 1) . ": [$departmentNames]";
            })->join(', ');

            throw ValidationException::withMessages([
                'iFrameSrc' => "The following groups of departments use different payment gateways: $departments. Please pay for each group separately or choose another payment method."
            ]);
        }
    }

    private function getDepartmentGateway(string $departmentId, GatewayTypeEnums $type): ?DepartmentGateway
    {
        /** @var DepartmentGateway $merchantGateway */
        $merchantGateway = $this->departmentGateways
            ->where('type', $type)
            ->where('department_id', $departmentId)
            ->first();

        return $merchantGateway;
    }

    /**
     * @throws ValidationException
     */
    private function checkoutMerchant(PaymentDepartment $paymentDepartment, array $transactionDetails): ResponseDto
    {
        $merchantGateway = $this->getDepartmentGateway(
            departmentId: $paymentDepartment->getAttribute('department_id'),
            type: GatewayTypeEnums::CARD_CONNECT_MERCHANT
        );

        $paymentTransaction = $paymentDepartment->getRelationValue('paymentTransactions')
            ->where('charge_type', PaymentTransactionChargeTypeEnums::BILL)
            ->first();

        $departmentId = $paymentDepartment->getAttribute('department_id');
        //Add custom field for department name
        $department = $this->departmentService->get($departmentId);
        $transactionDetails['userfields']['department_name'] = $department->getAttribute('name');

        // Add Cart items
        $transactionDetails['items'] = $this->checkoutMerchantItems($paymentDepartment);
        //Add the amount
        $transactionDetails['amount'] = $paymentDepartment->getAttribute('total_bill_amount');

        $this->setTransactionProcessingStatus(paymentTransaction: $paymentTransaction);

        $response = $this->transactionService->make(
            providerEnum: TransactionProvidersEnums::CARD_CONNECT_WEB,
            departmentGateway: $merchantGateway
        )->executeTransaction(
            transactionDetails: $transactionDetails,
            paymentTransactionId: $paymentTransaction->getKey(),
        );

        $this->setTransactionResponseStatus(paymentTransaction: $paymentTransaction, response: $response);

        return $response;
    }

    private function checkoutMerchantItems(PaymentDepartment $paymentDepartment): array
    {
        $paymentDepartmentBills = $paymentDepartment->getRelationValue('paymentDepartmentBills');
        // Add Cart items
        return $paymentDepartmentBills->map(function (PaymentDepartmentBill $paymentDepartmentBill) {
            $ref = $paymentDepartmentBill->getAttribute('bill_reference');
            $sub = $paymentDepartmentBill->getRelationValue('subDepartment')?->getAttribute('name');
            $amount = $paymentDepartmentBill->getAttribute('amount');
            return [
                'description' => "Account Reference: $ref" . ($sub ? ", Sub Department: $sub" : ""),
                'netamnt' => $amount,
            ];
        })->all();
    }

    /**
     * @throws ValidationException
     */
    private function checkoutFee(PaymentDepartment $paymentDepartment, array $transactionDetails): ?ResponseDto
    {
        //Add custom field for department name
        $amount = $paymentDepartment->getAttribute('total_fee_amount');
        if (!$amount > 0) {
            return null;
        }

        $paymentTransaction = $paymentDepartment->getRelationValue('paymentTransactions')
            ->where('charge_type', PaymentTransactionChargeTypeEnums::FEE)
            ->first();

        $feeGateway = $this->getDepartmentGateway(
            departmentId: $paymentDepartment->getAttribute('department_id'),
            type: GatewayTypeEnums::CARD_CONNECT_FEE
        );

        if (is_null($feeGateway)) {
            return null;
        }
        //Add the fee amount
        $transactionDetails['amount'] = $amount;
        $transactionDetails['items'] = [];

        $this->setTransactionProcessingStatus(paymentTransaction: $paymentTransaction);

        $response = $this->transactionService->make(
            providerEnum: TransactionProvidersEnums::CARD_CONNECT_WEB,
            departmentGateway: $feeGateway
        )->executeTransaction(
            transactionDetails: $transactionDetails,
            paymentTransactionId: $paymentTransaction->getKey(),
        );

        $this->setTransactionResponseStatus(paymentTransaction: $paymentTransaction, response: $response);

        return $response;
    }

    /**
     * @throws ValidationException
     */
    private function checkoutMerchantVoid(PaymentDepartment $paymentDepartment, ResponseDto $transactionMerchant): ResponseDto
    {
        $paymentTransaction = $paymentDepartment->getRelationValue('paymentTransactions')
            ->where('charge_type', PaymentTransactionChargeTypeEnums::BILL)
            ->first();

        $merchantGateway = $this->getDepartmentGateway(
            departmentId: $paymentDepartment->getAttribute('department_id'),
            type: GatewayTypeEnums::CARD_CONNECT_MERCHANT
        );

        $response = $this->transactionService->make(
            providerEnum: TransactionProvidersEnums::CARD_CONNECT_WEB,
            departmentGateway: $merchantGateway
        )->executeVoidTransaction(
            transactionDetails: [
                'reference_number' => $transactionMerchant->reference_number,
            ],
            paymentTransactionId: $paymentTransaction->getKey(),
        );

        $this->setTransactionProcessingReversalStatus(paymentTransaction: $paymentTransaction);

        return $response;
    }
}
