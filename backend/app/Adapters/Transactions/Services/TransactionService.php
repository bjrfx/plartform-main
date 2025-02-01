<?php

namespace App\Adapters\Transactions\Services;

use App\Adapters\Transactions\Factories\TransactionRepositoryFactory;
use App\Adapters\Transactions\Repositories\TransactionRepositoryInterface;
use App\Enums\Transaction\TransactionProvidersEnums;
use App\Models\Gateway\DepartmentGateway;
use Illuminate\Validation\ValidationException;

class TransactionService
{
    /**
     * @throws ValidationException
     */
    public function make(TransactionProvidersEnums $providerEnum, DepartmentGateway $departmentGateway): TransactionRepositoryInterface
    {
        return TransactionRepositoryFactory::make(
            providerEnum: $providerEnum,
            departmentGateway: $departmentGateway
        );
    }
}
