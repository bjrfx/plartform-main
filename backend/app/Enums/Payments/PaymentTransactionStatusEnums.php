<?php

namespace App\Enums\Payments;

enum PaymentTransactionStatusEnums: string
{
    /** Entry created but no action taken yet. */
    case PREPARED = 'PREPARED';

    /** Request sent to the 3rd party API and awaiting a response */
    case PROCESSING = 'PROCESSING';

    /** 3rd party API confirms successful processing */
    case SUCCESSFUL = 'SUCCESSFUL';

    /** Request to the 3rd party API fails. */
    case FAILED = 'FAILED';

    /** Request void. */
    case VOIDED = 'VOIDED';

    /** Request refunded. */
    case REFUNDED = 'REFUNDED';

    /** A reversal occurs when a fee transaction fails
     * Causing a previously successful bill transaction to be voided to ensure transactional integrity.
     */
    case REVERSAL = 'REVERSAL';
    /** Transaction was not executed due to a failure in a prior step (e.g., bill failure). */
    case NOT_EXECUTED = 'NOT_EXECUTED';
}
