<?php

namespace App\Helpers\General;

class NumberFormatHelper
{
    public static function make(?float $number = null, bool $thousandsComma = true, bool $nullable = true, int $decimal = 2): ?string
    {
        if ($nullable && is_null($number)) {
            return null;
        }
        if (is_null($number)) {
            $number = 0;
        }

        $thousandsSeparator = $thousandsComma ? "," : "";
        return number_format($number, $decimal, ".", $thousandsSeparator);
    }
}
