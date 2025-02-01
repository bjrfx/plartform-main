<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class AmountRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param \Closure(string, ?string=): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_null($value) && !preg_match('/^\d+(\.\d{1,2})?$/', $value)) {
            $fail("The $attribute must be a valid number with up to two decimal places.");
        }
    }
}
