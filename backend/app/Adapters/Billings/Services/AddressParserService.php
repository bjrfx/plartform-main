<?php

namespace App\Adapters\Billings\Services;

class AddressParserService
{
    protected array $unitKeywords = [];
    protected array $directionMap = [];
    protected array $suffixes = [];

    public function __construct()
    {
        $this->unitKeywords = config('platform.general.address_parser.unitKeywords');
        $this->directionMap = config('platform.general.address_parser.directionMap');
        $this->suffixes = config('platform.general.address_parser.suffixes');
    }
    public function parse(string $address): array
    {
        // Initialize components
        $parsedAddress = [
            'street_number' => null,
            'directional' => null,
            'street_name' => null,
            'street_suffix' => null,
            'unit_number' => null,
            'city' => null,
        ];
        // Normalize the address
        $address = trim(preg_replace('/\\s+/', ' ', strtolower($address)));

        // Break down the address into parts
        $parts = explode(' ', $address);
        $index = 0;

        // Identify the street number
        if (isset($parts[$index]) && is_numeric($parts[$index])) {
            $parsedAddress['street_number'] = $parts[$index];
            $index++;
        }

        // Identify the directional prefix (if any)
        foreach ($this->directionMap as $full => $variants) {
            if (isset($parts[$index]) && in_array(strtolower($parts[$index]), $variants)) {
                $parsedAddress['directional'] = $full;
                $index++;
                break;
            }
        }

        // Capture the street name until a suffix or unit keyword is found
        $streetNameParts = [];
        while (
            isset($parts[$index])
            &&
            !in_array(strtolower($parts[$index]), array_merge(...array_values($this->suffixes)))
            &&
            !in_array(strtolower($parts[$index]), $this->unitKeywords)
        ) {
            $streetNameParts[] = $parts[$index];
            $index++;
        }

        // Set the street name
        if (!empty($streetNameParts)) {
            $parsedAddress['street_name'] = implode(' ', $streetNameParts);
        }

        // Capture the street suffix
        foreach ($this->suffixes as $full => $variants) {
            if (isset($parts[$index]) && in_array(strtolower($parts[$index]), $variants)) {
                $parsedAddress['street_suffix'] = $full;
                $index++;
                break;
            }
        }

        // Capture the unit number (without the unit keyword)
        if (isset($parts[$index]) && in_array(strtolower($parts[$index]), $this->unitKeywords)) {
            $parsedAddress['unit_number'] = $parts[$index + 1] ?? '';
            $index += 2;
        }

        // The remaining part is considered the city
        if ($index < count($parts)) {
            $parsedAddress['city'] = implode(' ', array_slice($parts, $index));
        }

        return $parsedAddress;
    }
}
