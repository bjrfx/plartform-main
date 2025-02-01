<?php

namespace App\Helpers\General;

class PhoneFormatter
{
    /**
     * Format a phone number with the country code.
     *
     * @param string|null $phoneCode The country code (e.g., +1)
     * @param string|null $phoneNumber The phone number (e.g., 1234567890)
     * @return array Formatted phone number (e.g., +1 (123) 456-7890)
     */
    public static function format(?string $phoneCode, ?string $phoneNumber): array
    {
        if (is_null($phoneCode) || is_null($phoneNumber)) {
            return [
                'phone' => null,
                'tel' => null,
            ];
        }
        // Remove non-digits from the phone number
        $cleanNumber = preg_replace('/\D/', '', $phoneNumber);

        $phone = $phoneCode . $cleanNumber;
        $tel = $phone;
        // Validate the phone number length (assume 10 digits for standard North American numbers)
        if (strlen($cleanNumber) === 10) {
            $phone = sprintf(
                '%s (%s) %s-%s',
                $phoneCode,
                substr($cleanNumber, 0, 3),
                substr($cleanNumber, 3, 3),
                substr($cleanNumber, 6)
            );
        }

        return [
            'phone' => $phone,
            'tel' => $tel
        ];
    }
}
