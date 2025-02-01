<?php

namespace App\Adapters\Invoices\Services;

use App\Adapters\Invoices\Factories\InvoiceRepositoryFactory;
use App\Adapters\Invoices\Repositories\InvoiceRepositoryInterface;
use App\Enums\Invoice\InvoiceProvidersEnums;
use Illuminate\Validation\ValidationException;

class InvoiceService
{
    /**
     * @throws ValidationException
     */
    public function make(InvoiceProvidersEnums $providerEnum, object $gateway): InvoiceRepositoryInterface
    {
        return InvoiceRepositoryFactory::make(
            providerEnum: $providerEnum,
            gateway: $gateway
        );
    }

    public function hasInvoiceProvider(string $provider, object $department): bool
    {
        return InvoiceRepositoryFactory::hasInvoiceMethod(
            provider: $provider,
            department: $department
        );
    }
}
