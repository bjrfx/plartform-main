<?php

namespace App\Enums\Billings;

enum PaymentMethodTypesEnums: string
{
    case CREDIT = "credit";
    case DEBIT = "debit";
    case CHECK = "check";
}
