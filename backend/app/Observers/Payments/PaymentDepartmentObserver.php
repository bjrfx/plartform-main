<?php

namespace App\Observers\Payments;

use App\Enums\Payments\PaymentDepartmentStatusEnums;
use App\Helpers\General\NumberFormatHelper;
use App\Models\Payments\PaymentDepartment;
use Exception;
use Illuminate\Support\Str;
use Throwable;


class PaymentDepartmentObserver
{
    public function creating(PaymentDepartment $paymentDepartment): void
    {
        $this->assignTotalBill(paymentDepartment: $paymentDepartment);

        $paymentDepartment->setAttribute('status', PaymentDepartmentStatusEnums::PREPARED);
    }


    private function assignTotalBill(PaymentDepartment $paymentDepartment): void
    {
        $total = $paymentDepartment->getAttribute('total_bill_amount');
        $total += $paymentDepartment->getAttribute('total_fee_amount');

        $paymentDepartment->setAttribute('total_paid_amount', NumberFormatHelper::make($total, false));
    }
}
