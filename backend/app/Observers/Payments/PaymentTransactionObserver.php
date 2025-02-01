<?php

namespace App\Observers\Payments;

use App\Enums\Payments\PaymentTransactionStatusEnums;
use App\Helpers\General\DomainHelper;
use App\Models\Payments\PaymentTransaction;


class PaymentTransactionObserver
{
    public function creating(PaymentTransaction $paymentTransaction): void
    {
        $paymentTransaction->setAttribute('status', PaymentTransactionStatusEnums::PREPARED);
    }

    public function updating(PaymentTransaction $paymentTransaction): void
    {
        $this->assignStatusTime(paymentTransaction: $paymentTransaction);
    }

    private function assignStatusTime(PaymentTransaction $paymentTransaction): void
    {
        if ($paymentTransaction->wasChanged('status')) {
            $merchant = DomainHelper::getMerchant();
            if (is_null($merchant)) {
                $paymentDepartment = $paymentTransaction->getRelationValue('paymentDepartment');
                $department = $paymentDepartment->getRelationValue('department');
                $merchant = $department->getRelationValue('merchant');
            }
            $tz = $merchant->getAttribute('time_zone');
            $paymentTransaction->setAttribute('status_at_tz', now($tz));
        }
    }
}
