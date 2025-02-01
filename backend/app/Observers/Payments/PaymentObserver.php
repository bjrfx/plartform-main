<?php

namespace App\Observers\Payments;

use App\Models\Payments\Payment;
use Exception;
use Illuminate\Support\Str;
use Throwable;


class PaymentObserver
{
    public function creating(Payment $payment): void
    {
        $this->assignPaymentReference(payment: $payment);
    }

    /**
     * @throws Throwable
     */
    public function updating(Payment $payment): void
    {
        throw_if(
            $payment->isDirty('payment_reference'),
            Exception::class,
            'The payment_reference field cannot be updated.'
        );
    }

    private function assignPaymentReference(Payment $payment): void
    {
        do {
            // Generate a 10-character alphanumeric reference
            $date = now()->format('ymd');
            $reference = Str::random(7);
            $reference = Str::of($reference)->lower()->prepend($date);
        } while (
            Payment::query()
                ->where('payment_reference', $reference)
                ->exists());

        $payment->setAttribute('payment_reference', $reference);
    }
}
