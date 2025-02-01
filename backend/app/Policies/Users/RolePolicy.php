<?php

namespace App\Policies\Users;

use App\Acl\RoleHierarchy;
use App\Enums\Users\UserRoleEnums;
use App\Models\Departments\Department;
use App\Models\User;
use Exception;

class RolePolicy
{
    /**
     * Determine if the given user can assign the given role.
     */
    public function assignRole(User $loggedUser, User $user, string|UserRoleEnums $roleToAssign): bool
    {
        if (!($roleToAssign instanceof UserRoleEnums)) {
            try {
                $roleToAssign = UserRoleEnums::from($roleToAssign); // Convert if not an enum
            } catch (Exception $e) {
                // Log invalid role assignment attempt, if needed
                return false;
            }
        }

        $assignableRoles = RoleHierarchy::getAccessibleRolesFor($loggedUser->getAttribute('role')->value);

        return in_array($roleToAssign->value, $assignableRoles, true);
    }

    public function accessSystemFeatures(?User $loggedUser = null, ?User $user = null): bool
    {
        if (!$user) {
            return false;
        }

        return in_array($user->getAttribute('role'), [
            UserRoleEnums::SYSTEM_ADMIN,
            UserRoleEnums::ADMIN,
            UserRoleEnums::SUPPORT,
        ]);
    }

    public function accessMerchantFeatures(?User $loggedUser = null, ?User $user = null): bool
    {
        if (!$user) {
            return false;
        }

        return $user->getAttribute('role') !== UserRoleEnums::MEMBER;
    }

    public function accessMerchantDepartment(?User $loggedUser = null, ?Department $department = null): bool
    {
        if (!$loggedUser || !$department) {
            return false;
        }

        $role = $loggedUser->getAttribute('role');

        if ($role === UserRoleEnums::MEMBER) {
            return false;
        }
        if ($role !== UserRoleEnums::MERCHANT_STAFF) {
            return true;
        }

        //Directly querying the relationship
        return $loggedUser->departments()
            ->where('id', $department->getKey())
            ->exists();
    }

}
