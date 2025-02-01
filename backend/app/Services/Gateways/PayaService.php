<?php

namespace App\Services\Gateways;

use App\Adapters\Transactions\Dtos\ResponseDto;
use App\Adapters\Transactions\Services\TransactionService;
use App\Enums\Billings\PaymentMethodTypesEnums;
use App\Enums\Gateway\GatewayTypeEnums;
use App\Enums\Payments\PaymentTransactionChargeTypeEnums;
use App\Enums\Transaction\TransactionProvidersEnums;
use App\Models\Gateway\DepartmentGateway;
use App\Models\Gateway\Gateway;
use App\Models\Payments\Payment;
use App\Models\Payments\PaymentDepartment;
use App\Services\Departments\DepartmentService;
use App\Services\Gateways\Contracts\PaymentServiceContract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

/** @noinspection SpellCheckingInspection */

class PayaService extends PaymentServiceContract
{
    protected array $config = [];

    public function __construct(
        protected TransactionService $transactionService,
        protected DepartmentService  $departmentService
    )
    {
        $this->config = config('platform.gateways.department_gateways.paya');
    }

    public function index(): Collection
    {
        return Gateway::query()
            ->where('type', GatewayTypeEnums::PAYA)
            ->get();
    }

    public function save(array $requestData, ?Gateway $gateway = null): Gateway
    {
        $requestData['type'] = GatewayTypeEnums::PAYA;

        return $this->saveGateway(requestData: $requestData, gateway: $gateway);
    }

    public function fees(array $requestData): array
    {
        $items = $this->getDepartmentsFeesGateway(requestData: $requestData);

        $data = [
            'type' => PaymentMethodTypesEnums::CHECK,
            'fees' => [],
        ];

        foreach ($items as $item) {
            $data['fees'][] = $this->calculateDepartmentFee(item: $item, requestData: $requestData);
        }

        return $data;
    }

    /**
     * @throws ValidationException
     */
    public function checkout(Payment $payment, string $merchantName, mixed $paymentMetadata = null): array
    {
        $this->getDepartmentsGateways();

        $transactionDetails = [
            'amount' => 0,
            'custom' => [
                'payment_reference' => $payment->getAttribute('payment_reference'),//unique order ID for the transaction
                'department_name' => null,
                'transactionId' => null,
            ],
            'routingNumber' => Arr::get($paymentMetadata, 'check_aba'),
            'accountNumber' => Arr::get($paymentMetadata, 'check_account'),
            'accountType' => Arr::get($paymentMetadata, 'account_type'),
            'accountName' => Arr::get($paymentMetadata, 'check_name'),
        ];
        $transactionDetails['consumer'] = $this->assignTransactionDetails(
            payment: $payment,
        );

        $transactionDepartmentsStatus = [];

        $paymentDepartments = $payment->getRelationValue('paymentDepartments');

        foreach ($paymentDepartments as $paymentDepartment) {
            $departmentId = $paymentDepartment->getAttribute('department_id');
            //Add custom field for department name
            $department = $this->departmentService->get($departmentId);
            $transactionDetails['custom']['department_name'] = $department->getAttribute('name');

            $transactionMerchant = $this->checkoutMerchant(
                paymentDepartment: $paymentDepartment,
                transactionDetails: $transactionDetails,
            );

            $transactionMerchant->department_id = $departmentId;
            $transactionDepartmentsStatus[$departmentId] = $transactionMerchant;
        }

        return $transactionDepartmentsStatus;
    }

    private function assignTransactionDetails(Payment $payment): array
    {
        //fill Paya payer info
        return [
            'user_id' => $payment->getAttribute('user_id'),
            'firstName' => $payment->getAttribute('first_name'),
            'middleName' => $payment->getAttribute('middle_name'),
            'lastName' => $payment->getAttribute('last_name'),
            'address1' => $payment->getAttribute('address'),
            'address2' => $payment->getAttribute('address2'),
            'city' => $payment->getAttribute('city'),
            'state' => $payment->getAttribute('state'),
            'zip' => $payment->getAttribute('zip_code'),
            'phoneNumber' => $payment->getAttribute('phone'),
            'email' => $payment->getAttribute('email'),
        ];
    }

    /**
     * @throws ValidationException
     */
    private function checkoutMerchant(PaymentDepartment $paymentDepartment, array $transactionDetails): ResponseDto
    {
        $departmentId = $paymentDepartment->getAttribute('department_id');

        $merchantGateway = $this->getDepartmentGateway(
            departmentId: $departmentId,
        );

        $paymentTransaction = $paymentDepartment->getRelationValue('paymentTransactions')
            ->where('charge_type', PaymentTransactionChargeTypeEnums::BILL)
            ->first();


        //Add custom field for department name
        $transactionDetails['custom']['transactionId'] = $paymentTransaction->getKey();

        //Add the amount
        $transactionDetails['amount'] = $paymentDepartment->getAttribute('total_paid_amount');

        $this->setTransactionProcessingStatus(paymentTransaction: $paymentTransaction);

        $response = $this->transactionService->make(
            providerEnum: TransactionProvidersEnums::PAYA,
            departmentGateway: $merchantGateway
        )->executeTransaction(
            transactionDetails: $transactionDetails,
            paymentTransactionId: $paymentTransaction->getKey(),
        );

        $this->setTransactionResponseStatus(paymentTransaction: $paymentTransaction, response: $response);

        return $response;
    }

    private function getDepartmentGateway(string $departmentId): ?DepartmentGateway
    {
        /** @var DepartmentGateway $merchantGateway */
        $merchantGateway = $this->departmentGateways
            ->where('type', GatewayTypeEnums::PAYA)
            ->where('department_id', $departmentId)
            ->first();

        return $merchantGateway;
    }

    private function getDepartmentsFeesGateway(array $requestData): Collection
    {
        $this->getDepartmentsGateways();

        $departmentIds = Arr::pluck($requestData['cart'], 'department_id');
        $departmentIds = array_unique($departmentIds);

        return $this->departmentGateways
            ->where('type', GatewayTypeEnums::PAYA)
            ->whereIn('department_id', $departmentIds);
    }

    private function calculateDepartmentFee(DepartmentGateway $item, array $requestData): array
    {

        $departmentId = $item->getAttribute('department_id');
        $carts = Arr::get($requestData, 'cart');
        $total = collect($carts)->where('department_id', $departmentId)->sum('amount');
        $total = round($total, 2);

        $additionalData = $item->getAttribute('additional_data');

        $fee = [
            'department_id' => $departmentId,
            'amount' => 0,
        ];
        if ($total >= Arr::get($this->config, 'big_amount')) {
            $fee['amount'] = Arr::get($additionalData, 'fee_amount_big', 0);
        }
        if ($fee['amount'] > 0) {
            return $fee;
        }

        $fee['amount'] = Arr::get($additionalData, 'fee_amount');
        $feePercentage = Arr::get($additionalData, 'fee_percentage', 0);
        if ($feePercentage > 0) {
            $fee['amount'] += ($total * $feePercentage / 100);
        }
        return $fee;
    }
}
