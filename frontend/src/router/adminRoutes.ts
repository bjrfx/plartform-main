// adminRoutes.ts
import {RouteRecordRaw} from 'vue-router';

export const adminRoutes: Array<RouteRecordRaw> = [
    {
        path: '/admin',
        name: 'MerchantDashboard',
        component: () => import('@/views/platform/merchant/MerchantDashboard.vue'),
        meta: {role: '*'}, // Accessible by all roles
    },
    {
        path: '/admin/users',
        name: 'UsersList',
        component: () => import('@/views/platform/merchant/users/UsersList.vue'),
        meta: {
            role: '*',
            breadcrumb: 'Users List', // Label for the breadcrumb
            parent: 'MerchantDashboard', // Reference the parent route
        },
        //meta: { requiresAuth: true, role: ['system_admin', 'admin', 'support'] },
    },
    {
        path: '/admin/users/edit/:userId?',
        name: 'UserEdit',
        component: () => import('@/views/platform/merchant/users/UserEdit.vue'),
        meta: {
            role: '*',
            breadcrumb: 'User :user', // Label for the breadcrumb
            parent: 'UsersList', // Reference the parent route
        },
        //meta: { requiresAuth: true, role: ['system_admin', 'admin', 'support'] },
    },
];