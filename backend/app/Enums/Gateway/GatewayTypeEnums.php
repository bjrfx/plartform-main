<?php

namespace App\Enums\Gateway;

enum GatewayTypeEnums: string
{
    /** @noinspection SpellCheckingInspection Needed for "PAYA" */
    case PAYA = "PAYA";
    case CARD_CONNECT_MERCHANT = "CARD_CONNECT_MERCHANT";
    case CARD_CONNECT_FEE = "CARD_CONNECT_FEE";
    case DIRECT_STATEMENT = "DIRECT_STATEMENT";
    case TYLER = "TYLER";
    case URL_QUERY_PAY = "URL_QUERY_PAY";
}
