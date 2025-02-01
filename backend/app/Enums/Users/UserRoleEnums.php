<?php

namespace App\Enums\Users;

enum UserRoleEnums: string
{
    case SYSTEM_ADMIN = 'SYSTEM_ADMIN';
    case ADMIN = 'ADMIN';
    case SUPPORT = 'SUPPORT';
    case MERCHANT_ADMIN = 'MERCHANT_ADMIN';
    case MERCHANT_STAFF = 'MERCHANT_STAFF';
    case MEMBER = 'MEMBER'; // Basic registered user

    // Helper method to return all values
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
