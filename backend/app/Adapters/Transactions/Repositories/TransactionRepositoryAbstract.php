<?php

namespace App\Adapters\Transactions\Repositories;

use App\Models\Gateway\DepartmentGateway;
use App\Models\Payments\PaymentTransaction;
use App\Models\Payments\PaymentTransactionLog;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

abstract class TransactionRepositoryAbstract implements TransactionRepositoryInterface
{

    public ?string $apiUrl = null;
    public ?string $username = null;
    public ?string $password = null;

    public array $config = [];

    public function __construct(DepartmentGateway $departmentGateway)
    {
        $this->apiUrl = rtrim($departmentGateway->getAttribute('custom_url'), '/');
        $this->username = $departmentGateway->getAttribute('username');
        $this->password = $departmentGateway->getAttribute('password');

        if (!is_null($departmentGateway->getAttribute('gateway_id'))) {
            $this->apiUrl = $departmentGateway->getRelationValue('gateway')?->getAttribute('base_url');
        }

        $this->config = config('platform.gateways.department_gateways');
    }

    protected function assignAuthorization(): string
    {
        return base64_encode($this->username . ':' . $this->password);
    }

    protected function saveApiLog(string $paymentTransactionId, string $action, bool $success = true, array $requestData, array $responseData = []): void
    {
        $save = [
            'payment_transaction_id' => $paymentTransactionId,
            'action' => $action,
            'success' => $success,
            'request' => $this->assignMask($requestData),
            'response' => $this->assignMask($responseData),
        ];

        PaymentTransactionLog::query()->create($save);
    }

    private function assignMask(array $data): array
    {
        $keys = [
            'token',
            'account',
        ];
        foreach ($keys as $key) {
            $value = Arr::get($data, $key);
            if (!is_null($value)) {
                //mask last 4 calculates
                $data[$key] = Str::mask($value, '*', strlen($value) - 4, 4);
            }
        }

        return $data;
    }
}
