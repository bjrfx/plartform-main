import {defineStore} from 'pinia';
import {UserRole} from '@/enums/enums';

interface RoleOption {
    value: UserRole;
    label: string;
}

type RolesState = {
    [key in UserRole]: RoleOption[];
};

export const useRoleStore = defineStore('roles', {
    state: (): { roles: RolesState } => ({
        roles: {
            [UserRole.SYSTEM_ADMIN]: [
                {value: UserRole.MEMBER, label: 'Member'},
                {value: UserRole.MERCHANT_STAFF, label: 'Merchant Staff'},
                {value: UserRole.MERCHANT_ADMIN, label: 'Merchant Admin'},
                {value: UserRole.SUPPORT, label: 'Support'},
                {value: UserRole.ADMIN, label: 'Admin'},
                {value: UserRole.SYSTEM_ADMIN, label: 'System Admin'},
            ],
            [UserRole.ADMIN]: [
                {value: UserRole.MEMBER, label: 'Member'},
                {value: UserRole.MERCHANT_STAFF, label: 'Merchant Staff'},
                {value: UserRole.MERCHANT_ADMIN, label: 'Merchant Admin'},
                {value: UserRole.SUPPORT, label: 'Support'},
            ],
            [UserRole.SUPPORT]: [
                {value: UserRole.MEMBER, label: 'Member'},
                {value: UserRole.MERCHANT_STAFF, label: 'Merchant Staff'},
                {value: UserRole.MERCHANT_ADMIN, label: 'Merchant Admin'},
            ],
            [UserRole.MERCHANT_ADMIN]: [
                {value: UserRole.MEMBER, label: 'Member'},
                {value: UserRole.MERCHANT_STAFF, label: 'Merchant Staff'},
            ],
            [UserRole.MERCHANT_STAFF]: [
                {value: UserRole.MEMBER, label: 'Member'},
            ],
            [UserRole.MEMBER]: [],
        },
    }),

    actions: {
        getAllRoles(): RoleOption[] {
            return this.roles[UserRole.SYSTEM_ADMIN];
        },
        getRoles(role: UserRole): RoleOption[] {
            return this.roles[role] ?? [];
        },
        isSystemRole(role: UserRole): boolean {
            const roles: UserRole[] = [
                UserRole.SYSTEM_ADMIN,
                UserRole.ADMIN,
                UserRole.SUPPORT,
            ];
            return roles.includes(role);
        },
        isMerchantRole(role: UserRole): boolean {
            const roles: UserRole[] = [
                UserRole.MERCHANT_ADMIN,
                UserRole.MERCHANT_STAFF,
            ];
            return roles.includes(role);
        },
    },
});