<?php

namespace App\Adapters\Billings\Factories;

use App\Adapters\Billings\Repositories\Tyler\TylerJsonApiRepository;
use App\Adapters\Billings\Repositories\Tyler\TylerXmlApiRepository;
use App\Enums\Billings\BillingProvidersEnums;
use Illuminate\Validation\ValidationException;

class BillingRepositoryFactory
{
    /**
     * @throws ValidationException
     */
    public static function make(BillingProvidersEnums $providerEnum, object $gateway): object
    {
        return match ($providerEnum) {
            BillingProvidersEnums::TYLER_XML => new TylerXmlApiRepository(gateway: $gateway),
            BillingProvidersEnums::TYLER_JSON => new TylerJsonApiRepository(gateway: $gateway),
            default => throw ValidationException::withMessages([
                'BillingRepositoryFactory' => "Unsupported provider: {$providerEnum->value}"
            ]),
        };
    }
}
