<?php

namespace App\Services\Payments;

use App\Enums\Billings\PaymentMethodTypesEnums;
use App\Enums\Gateway\GatewayTypeEnums;
use App\Enums\Payments\PaymentTransactionChargeTypeEnums;
use App\Helpers\General\CacheKeysHelper;
use App\Helpers\General\DomainHelper;
use App\Helpers\General\NumberFormatHelper;
use App\Models\Gateway\DepartmentGateway;
use App\Models\Merchants\Merchant;
use App\Models\Payments\Payment;
use App\Models\Payments\PaymentDepartment;
use App\Services\Gateways\CardConnectService;
use App\Services\Gateways\PayaService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection as SimpleCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class PaymentService
{

    private ?Merchant $merchant;
    private ?CardConnectService $cardConnectService;
    private ?PayaService $payaService;

    private ?Payment $payment = null;
    private ?PaymentMethodTypesEnums $type;
    private ?SimpleCollection $cart;
    private ?SimpleCollection $fees;

    public function __construct(CardConnectService $cardConnectService, PayaService $payaService)
    {
        $this->merchant = DomainHelper::getMerchant();
        $this->cardConnectService = $cardConnectService;
        $this->payaService = $payaService;
    }

    /**
     * @throws ValidationException
     */
    public function handle(array $requestData): array
    {
        $type = Arr::get($requestData, 'type');
        $this->type = PaymentMethodTypesEnums::from($type);

        $cart = Arr::get($requestData, 'cart');
        $this->cart = collect($cart);

        $fees = Arr::get($requestData, 'fees');
        $this->fees = collect($fees);

        try {
            DB::transaction(function () use ($requestData) {

                $this->createPayment(requestData: $requestData);
                $this->processDepartments();
            });
        } catch (Throwable $e) {
            throw ValidationException::withMessages([
                'payment' => "No charge was made, but something went wrong with your payment. Please try again or contact support if the issue persists.",
                'error' => $e->getMessage(),
            ]);
        }

        if ($this->type === PaymentMethodTypesEnums::CHECK) {
            $paymentMetadata = Arr::get($requestData, 'ach', []);
        } elseif (in_array($this->type, [PaymentMethodTypesEnums::CHECK, PaymentMethodTypesEnums::DEBIT])) {
            $paymentMetadata = Arr::get($requestData, 'token.token');
        }

        return $this->sendTransactions(paymentMetadata: $paymentMetadata);
    }

    private function createPayment(array $requestData): void
    {
        $hsn = Arr::get($requestData, 'hsn');

        $save = [
            'created_at_tz' => now($this->merchant->getAttribute('time_zone')),
            'payment_method' => Arr::get($requestData, 'type'),
            'user_id' => $hsn ? null : auth()->id(),
            'card_owner' => Arr::get($requestData, 'payer.card_owner'),
            'first_name' => Arr::get($requestData, 'payer.first_name'),
            'middle_name' => Arr::get($requestData, 'payer.middle_name'),
            'last_name' => Arr::get($requestData, 'payer.last_name'),
            'email' => Arr::get($requestData, 'payer.email'),
            'phone' => Arr::get($requestData, 'payer.phone'),
            'address' => Arr::get($requestData, 'payer.address'),
            'address_2' => Arr::get($requestData, 'payer.address_2'),
            'city' => Arr::get($requestData, 'payer.city'),
            'state' => Arr::get($requestData, 'payer.state'),
            'zip_code' => Arr::get($requestData, 'payer.zip_code'),//card_owner
            'ip_address' => request()->getClientIp(),
            'user_agent' => request()->header('user-agent'),
        ];

        /** @var Payment $payment */
        $payment = $this->merchant->payments()->create($save);

        $this->payment = $payment;
    }

    private function processDepartments(): void
    {
        $departmentIds = $this->cart->pluck('department_id')->unique()->all();
        foreach ($departmentIds as $departmentId) {
            $paymentDepartment = $this->createPaymentDepartments(
                departmentId: $departmentId,
            );
            $this->createPaymentDepartmentBills(paymentDepartment: $paymentDepartment);
            $this->createPaymentTransactions(paymentDepartment: $paymentDepartment);
        }
    }

    private function createPaymentDepartments(string $departmentId): PaymentDepartment
    {
        $totalBill = $this->cart->where('department_id', $departmentId)->sum('amount');
        $totalFee = $this->fees->where('department_id', $departmentId)->sum('amount');

        $baseFee = $this->getBaseFeesAmount(departmentId: $departmentId, totalBill: $totalBill);

        $save = [
            'department_id' => $departmentId,
            'total_bill_amount' => NumberFormatHelper::make($totalBill, false),
            'total_fee_amount' => NumberFormatHelper::make($totalFee, false),
            'base_fee_amount' => NumberFormatHelper::make($baseFee['base_fee_amount'], false),
            'base_fee_percentage' => NumberFormatHelper::make($baseFee['base_fee_percentage'], false),
        ];

        return $this->payment->paymentDepartments()->create($save);
    }

    private function getDepartmentFeesGateway(string $departmentId): DepartmentGateway
    {
        $departmentGateways = $this->getDepartmentsGateways();

        /** @var DepartmentGateway $departmentGateway */
        $departmentGateway = $departmentGateways
            ->where('type', GatewayTypeEnums::CARD_CONNECT_FEE)
            ->where('department_id', $departmentId)
            ->first();

        return $departmentGateway;
    }

    private function getDepartmentsGateways(): Collection
    {
        $merchantId = $this->merchant->getKey();

        $key = CacheKeysHelper::getCardConnectCacheKey($merchantId);

        return cache()->remember(
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

    private function getBaseFeesAmount(string $departmentId, float $totalBill): array
    {
        $departmentGateway = $this->getDepartmentFeesGateway(departmentId: $departmentId);
        $additionalData = $departmentGateway->getAttribute('additional_data');

        return $this->calculateBaseFees(departmentId: $departmentId, totalBill: $totalBill, additionalData: $additionalData);
    }

    private function calculateBaseFees(string $departmentId, float $totalBill, array $additionalData): array
    {
        $data = [
            'base_fee_amount' => 0,
            'base_fee_percentage' => 0,
        ];

        if ($this->isCreditOrDebitSameFee($additionalData)) {
            return $this->calculateCreditFees($data, $additionalData, $totalBill);
        }

        if ($this->type === PaymentMethodTypesEnums::DEBIT) {
            return $this->calculateDebitFees($data, $additionalData, $totalBill);
        }

        if ($this->type === PaymentMethodTypesEnums::CHECK) {
            return $this->calculateCheckFees($data, $departmentId, $additionalData);
        }

        return $data;
    }

    private function isCreditOrDebitSameFee(array $additionalData): bool
    {
        if ($this->type === PaymentMethodTypesEnums::CREDIT) {
            return true;
        }
        return $this->type === PaymentMethodTypesEnums::DEBIT && Arr::get($additionalData, 'has_same_fee', false);
    }

    private function calculateCreditFees(array $data, array $additionalData, float $totalBill): array
    {
        $data['base_fee_amount'] = (float)Arr::get($additionalData, 'credit_card_min', 0);
        $fee = Arr::get($additionalData, 'credit_card_amount', 0);
        if ($fee > 0 && $data['base_fee_amount'] <= $totalBill) {
            $data['base_fee_amount'] = $fee;
        }
        $data['base_fee_percentage'] = Arr::get($additionalData, 'credit_card_percentage', 0);

        return $data;
    }

    private function calculateDebitFees(array $data, array $additionalData, float $totalBill): array
    {
        $data['base_fee_amount'] = (float)Arr::get($additionalData, 'debit_card_amount', 0);
        $fee = Arr::get($additionalData, 'debit_card_amount', 0);
        if ($fee > 0 && $data['base_fee_amount'] <= $totalBill) {
            $data['base_fee_amount'] = $fee;
        }
        $data['base_fee_percentage'] = Arr::get($additionalData, 'debit_card_percentage', 0);

        return $data;
    }

    private function calculateCheckFees(array $data, string $departmentId, array $additionalData): array
    {
        $total = $this->cart->where('department_id', $departmentId)->sum('amount');
        $total = round($total, 2);
        $fee = 0;
        if ($total >= 30000) {
            $fee = Arr::get($additionalData, 'fee_amount_big', 0);
        }
        if ($fee > 0) {
            $data['base_fee_amount'] = $fee;
        } else {
            $data['base_fee_amount'] = (float)Arr::get($additionalData, 'fee_amount', 0);
        }
        return $data;
    }

    ///

    private function createPaymentDepartmentBills(PaymentDepartment $paymentDepartment): void
    {
        foreach ($this->cart as $cart) {
            $amount = Arr::get($cart, 'amount');
            $amount = NumberFormatHelper::make($amount, false);
            $save = [
                'sub_department_id' => Arr::get($cart, 'sub_department_id'),
                'bill_reference' => Arr::get($cart, 'account_reference'),
                'amount' => $amount,
                'bill_payload' => Arr::get($cart, 'form_payload'),
            ];

            $paymentDepartment->paymentDepartmentBills()->create($save);
        }
    }

    private function createPaymentTransactions(PaymentDepartment $paymentDepartment): void
    {
        if ($this->type === PaymentMethodTypesEnums::CHECK) {
            //On Check - we send a single transaction of the total paid
            $amount = $paymentDepartment->getAttribute('total_paid_amount');
        } else {
            $amount = $paymentDepartment->getAttribute('total_bill_amount');
        }
        //create bill entry
        $save = [
            'charge_type' => PaymentTransactionChargeTypeEnums::BILL,
            'payment_method' => $this->type,
            'amount' => $amount
        ];
        $paymentDepartment->paymentTransactions()->create($save);

        $this->createPaymentFeeTransaction(paymentDepartment: $paymentDepartment);
    }

    private function createPaymentFeeTransaction(PaymentDepartment $paymentDepartment): void
    {
        $amount = $paymentDepartment->getAttribute('total_fee_amount');
        //If it's not a Check and there is a fee amount
        if ($this->type !== PaymentMethodTypesEnums::CHECK && $amount > 0) {
            //create fee entry
            $save = [
                'charge_type' => PaymentTransactionChargeTypeEnums::FEE,
                'payment_method' => $this->type,
                'amount' => $amount,
            ];
            $paymentDepartment->paymentTransactions()->create($save);
        }
    }

    /**
     * @throws ValidationException
     */
    private function sendTransactions(mixed $paymentMetadata): array
    {
        $merchantName = $this->merchant->getAttribute('name');
        //Load all relation before processing
        $this->payment->load(['paymentDepartments' => function (\Illuminate\Contracts\Database\Eloquent\Builder $query) {
            $query->with([
                'paymentTransactions',
                'paymentDepartmentBills' => fn($paymentDepartmentBills) => $paymentDepartmentBills->with('subDepartment'),
            ]);
        }]);

        if (in_array($this->type, [PaymentMethodTypesEnums::DEBIT, PaymentMethodTypesEnums::CREDIT])) {
            return $this->cardConnectService->checkout(payment: $this->payment, merchantName: $merchantName, paymentMetadata: $paymentMetadata);
        }
        if ($this->type === PaymentMethodTypesEnums::CHECK) {
            return $this->payaService->checkout(payment: $this->payment, merchantName: $merchantName, paymentMetadata: $paymentMetadata);
        }
        return [];
    }
}
