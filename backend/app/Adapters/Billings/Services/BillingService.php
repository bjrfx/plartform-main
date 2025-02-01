<?php

namespace App\Adapters\Billings\Services;

use App\Adapters\Billings\Factories\BillingRepositoryFactory;
use App\Enums\Billings\BillingProvidersEnums;
use Illuminate\Validation\ValidationException;

class BillingService
{
    /**
     * @throws ValidationException
     */
    public static function make(BillingProvidersEnums $providerEnum, object $gateway): object
    {
        return BillingRepositoryFactory::make(
            providerEnum: $providerEnum,
            gateway: $gateway
        );
    }
}
