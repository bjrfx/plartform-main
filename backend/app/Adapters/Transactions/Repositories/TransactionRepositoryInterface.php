<?php

namespace App\Adapters\Transactions\Repositories;

use App\Adapters\Transactions\Dtos\ResponseDto;
use App\Enums\Billings\PaymentMethodTypesEnums;

interface TransactionRepositoryInterface
{
    public function executeTransaction(array $transactionDetails, string $paymentTransactionId): ResponseDto;

    public function executeVoidTransaction(array $transactionDetails, string $paymentTransactionId): ResponseDto;

    public function executeRefundTransaction(array $transactionDetails, string $paymentTransactionId): ResponseDto;

    public function getType(string $token): ?PaymentMethodTypesEnums;
}
