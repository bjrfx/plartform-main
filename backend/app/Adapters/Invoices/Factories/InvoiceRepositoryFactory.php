<?php

namespace App\Adapters\Invoices\Factories;

use App\Enums\Invoice\InvoiceProvidersEnums;
use App\Adapters\Invoices\Repositories\DirectStatement\DirectStatementInvoiceApiRepository;
use App\Adapters\Invoices\Repositories\InvoiceRepositoryInterface;

class InvoiceRepositoryFactory
{
    public static function make(InvoiceProvidersEnums $providerEnum, object $gateway): InvoiceRepositoryInterface
    {
        return match ($providerEnum) {
            InvoiceProvidersEnums::DIRECT_STATEMENT => new DirectStatementInvoiceApiRepository(gateway: $gateway),
        };
    }

    /**
     * Check if a given merchant slug has an associated invoice method.
     */
    public static function hasInvoiceMethod(string $provider, object $department): bool
    {
        $providerSlug = InvoiceProvidersEnums::tryFrom($provider);
        return match ($providerSlug) {
            InvoiceProvidersEnums::DIRECT_STATEMENT => !is_null($department->getRelationValue('invoiceDirectStatementGateway')),
            default => false,
        };
    }
}
