<?php

namespace App\Enums\Transaction;

enum TransactionProvidersEnums: string
{
    /** @noinspection SpellCheckingInspection Needed for "PAYA" */
    case PAYA = "PAYA";
    case CARD_CONNECT_WEB = "CARD_CONNECT_WEB";
    case CARD_CONNECT_TERMINAL = "CARD_CONNECT_TERMINAL";
}
