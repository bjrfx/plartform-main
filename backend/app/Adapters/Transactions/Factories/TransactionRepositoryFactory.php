<?php

namespace App\Adapters\Transactions\Factories;

use App\Adapters\Transactions\Repositories\CardConnect\CardConnectApiRepository;
use App\Adapters\Transactions\Repositories\CardConnect\CardConnectApiTerminalRepository;
use App\Adapters\Transactions\Repositories\Paya\PayaApiRepository;
use App\Adapters\Transactions\Repositories\TransactionRepositoryAbstract;
use App\Enums\Transaction\TransactionProvidersEnums;
use App\Models\Gateway\DepartmentGateway;
use Illuminate\Validation\ValidationException;

class TransactionRepositoryFactory
{
    /**
     * @throws ValidationException
     */
    public static function make(TransactionProvidersEnums $providerEnum, DepartmentGateway $departmentGateway): TransactionRepositoryAbstract
    {
        return match ($providerEnum) {
            TransactionProvidersEnums::PAYA => new PayaApiRepository(departmentGateway: $departmentGateway),
            TransactionProvidersEnums::CARD_CONNECT_WEB => new CardConnectApiRepository(departmentGateway: $departmentGateway),
            TransactionProvidersEnums::CARD_CONNECT_TERMINAL => new CardConnectApiTerminalRepository(departmentGateway: $departmentGateway),
        };
    }

    /**
     * Check if a given department has an associated Transaction method.
     */
    public static function hasTransactionMethod(string $provider, object $department): bool
    {
        $providerSlug = TransactionProvidersEnums::tryFrom($provider);
        /** @noinspection SpellCheckingInspection */
        return match ($providerSlug) {
            TransactionProvidersEnums::PAYA => !is_null($department->getRelationValue('payaGateway')),
            TransactionProvidersEnums::CARD_CONNECT_WEB => $department->getRelationValue('cardConnectGateway')->count() > 0,
            TransactionProvidersEnums::CARD_CONNECT_TERMINAL => $department->getRelationValue('cardConnectTerminalGateway')->count() > 0,
            default => false,
        };
    }
}
