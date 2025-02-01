<?php

namespace App\Enums\Forms;

enum HostedPaymentsTypeEnums: string
{
    case TEXT = "TEXT";
    case TEXTAREA = "TEXTAREA";
    case AMOUNT = "AMOUNT";
    case PHONE = "PHONE";
    case REFERENCE = "REFERENCE";
    case DIVIDER = "DIVIDER";


    // Helper method to return all values
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
