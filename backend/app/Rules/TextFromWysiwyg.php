<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class TextFromWysiwyg implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param \Closure(string, ?string=): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Strip HTML tags from the input
        $strippedValue = strip_tags($value);

        // Remove spaces and line breaks to ensure actual content exists
        $cleanedValue = trim(preg_replace('/\s+/', '', $strippedValue));

        // If the stripped value is empty or contains only whitespace, fail the validation
        if (strlen($cleanedValue) === 0) {
            $fail(__('The :attribute cannot be without text.'));
        }
    }
}
