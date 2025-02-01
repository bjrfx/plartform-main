<?php

namespace App\Enums\Payments;

enum PaymentTransactionChargeTypeEnums: string
{
    case BILL = 'bill';
    case FEE = 'fee';
}
