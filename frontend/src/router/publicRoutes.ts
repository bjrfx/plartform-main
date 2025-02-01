import {RouteRecordRaw} from 'vue-router';

export const publicRoutes: Array<RouteRecordRaw> = [
    {
        path: '/',
        name: 'WelcomePage',
        component: () => import('@/views/WelcomePage.vue'),
        meta: {role: '*'}, // Accessible by all roles
    },
    {
        path: '/:slug',
        name: 'DepartmentPayment',
        component: () => import('@/views/platform/merchant/departments/DepartmentPayment.vue'),
        meta: {
            role: '*',
            breadcrumb: ':department', // Label for the breadcrumb
            parent: 'WelcomePage', // Reference the parent route
        },
        //meta: { requiresAuth: true, role: ['system_admin', 'admin', 'support'] },
    },
    {
        path: '/select-department',
        name: 'DepartmentsAndCart',
        component: () => import('@/views/platform/merchant/checkout/DepartmentsAndCart.vue'),
        meta: {
            role: '*',
            breadcrumb: ':department', // Label for the breadcrumb
            parent: 'WelcomePage', // Reference the parent route
        },
        //meta: { requiresAuth: true, role: ['system_admin', 'admin', 'support'] },
    },
    {
        path: '/checkout',
        name: 'Checkout',
        component: () => import('@/views/platform/merchant/checkout/Checkout.vue'),
        meta: {
            role: '*',
            breadcrumb: 'Checkout', // Label for the breadcrumb
            parent: 'WelcomePage', // Reference the parent route
        },
        //meta: { requiresAuth: true, role: ['system_admin', 'admin', 'support'] },
    },
    /** Global fallback */
    {
        path: '/:pathMatch(.*)*', // Fallback - Matches all routes not defined above
        name: 'NotFound',
        component: () => import('@/views/NotFound.vue'),
    },
];