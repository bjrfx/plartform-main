<?php

namespace App\Acl;

use App\Enums\Users\UserRoleEnums;
use Exception;

class RoleHierarchy
{
    public static function getHierarchy(): array
    {
        return [
            UserRoleEnums::SYSTEM_ADMIN->value => [
                UserRoleEnums::SYSTEM_ADMIN->value,
                UserRoleEnums::ADMIN->value,
                UserRoleEnums::SUPPORT->value,
                UserRoleEnums::MERCHANT_ADMIN->value,
                UserRoleEnums::MERCHANT_STAFF->value,
                UserRoleEnums::MEMBER->value,
            ],
            UserRoleEnums::ADMIN->value => [
                UserRoleEnums::SUPPORT->value,
                UserRoleEnums::MERCHANT_ADMIN->value,
                UserRoleEnums::MERCHANT_STAFF->value,
                UserRoleEnums::MEMBER->value,
            ],
            UserRoleEnums::SUPPORT->value => [
                UserRoleEnums::MERCHANT_ADMIN->value,
                UserRoleEnums::MERCHANT_STAFF->value,
                UserRoleEnums::MEMBER->value,
            ],
            UserRoleEnums::MERCHANT_ADMIN->value => [
                UserRoleEnums::MERCHANT_STAFF->value,
                UserRoleEnums::MEMBER->value,
            ],
            UserRoleEnums::MERCHANT_STAFF->value => [
                UserRoleEnums::MEMBER->value,
            ],
        ];
    }

    public static function getAccessibleRolesFor(string|UserRoleEnums $role): array
    {
        if (!($role instanceof UserRoleEnums)) {
            try {
                $role = UserRoleEnums::from($role); // Convert if not an enum
            } catch (Exception $e) {
                // Log invalid role assignment attempt, if needed
                return [];
            }
        }
        return self::getHierarchy()[$role->value] ?? [];
    }
}
