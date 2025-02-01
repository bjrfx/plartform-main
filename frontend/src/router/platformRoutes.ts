// platformRoutes.ts
import {RouteRecordRaw} from 'vue-router';

export const platformRoutes: Array<RouteRecordRaw> = [
    {
        path: '/platform',
        name: 'AdminWelcomePage',
        component: () => import('@/views/AdminWelcomePage.vue'),
        meta: {role: '*'},
        //meta: { requiresAuth: false, role: ['system_admin', 'admin', 'support'] },
    },
    {
        path: '/platform/dashboard',
        name: 'SystemDashboard',
        component: () => import('@/views/platform/SystemDashboard.vue'),
        meta: {
            role: '*',
            breadcrumb: 'Dashboard',
        },
        //meta: { requiresAuth: true, role: ['system_admin', 'admin', 'support'] },
    },
    {
        path: '/platform/hosted-payments',
        name: 'HostedPaymentsEdit',
        component: () => import('@/views/platform/HostedPaymentsEdit.vue'),
        meta: {
            role: '*',
            breadcrumb: 'Hosted Payments Form Default Fields', // Label for the breadcrumb
            parent: 'SystemDashboard', // Reference the parent route
        },
        //meta: { requiresAuth: true, role: ['system_admin', 'admin', 'support'] },
    },
    {
        path: '/platform/users',
        name: 'AllUsersList',
        component: () => import('@/views/platform/UsersList.vue'),
        meta: {
            role: '*',
            breadcrumb: 'Users List', // Label for the breadcrumb
            parent: 'SystemDashboard', // Reference the parent route
        },
        //meta: { requiresAuth: true, role: ['system_admin', 'admin', 'support'] },
    },
    {
        path: '/platform/users/edit/:userId?',
        name: 'SystemUserEdit',
        component: () => import('@/views/platform/UserEdit.vue'),
        meta: {
            role: '*',
            breadcrumb: 'User :user', // Label for the breadcrumb
            parent: 'AllUsersList', // Reference the parent route
        },
        //meta: { requiresAuth: true, role: ['system_admin', 'admin', 'support'] },
    },
    //Icons
    {
        path: '/platform/icons',
        name: 'AllIconsList',
        component: () => import('@/views/platform/IconsList.vue'),
        meta: {
            role: '*',
            breadcrumb: 'Icons', // Label for the breadcrumb
            parent: 'SystemDashboard', // Reference the parent route
        },
        //meta: { requiresAuth: true, role: ['system_admin', 'admin', 'support'] },
    },
    {
        path: '/platform/icons/edit/:iconId?',
        name: 'IconEdit',
        component: () => import('@/views/platform/IconEdit.vue'),
        meta: {
            role: '*',
            breadcrumb: 'Icon', // Label for the breadcrumb
            parent: 'AllIconsList', // Reference the parent route
        },
        //meta: { requiresAuth: true, role: ['system_admin', 'admin', 'support'] },
    },
    {
        path: '/platform/notification-billings',
        name: 'SystemNotificationBillingEdit',
        component: () => import('@/views/platform/NotificationBillingEdit.vue'),
        meta: {
            role: '*',
            breadcrumb: 'Billing Notification Default Content', // Label for the breadcrumb
            parent: 'SystemDashboard', // Reference the parent route
        },
        //meta: { requiresAuth: true, role: ['system_admin', 'admin', 'support'] },
    },
    {
        path: '/platform/gateways-paya',
        name: 'SystemPayaList',
        component: () => import('@/views/platform/gateways/PayaList.vue'),
        meta: {
            role: '*',
            breadcrumb: 'Gateways - Paya', // Label for the breadcrumb
            parent: 'SystemDashboard', // Reference the parent route
        },
        //meta: { requiresAuth: true, role: ['system_admin', 'admin', 'support'] },
    },
    {
        path: '/platform/gateways-paya/edit/:gatewayId?',
        name: 'SystemPayaEdit',
        component: () => import('@/views/platform/gateways/PayaEdit.vue'),
        meta: {
            role: '*',
            breadcrumb: ':name', // Label for the breadcrumb
            parent: 'SystemPayaList', // Reference the parent route
        },
        //meta: { requiresAuth: true, role: ['system_admin', 'admin', 'support'] },
    },
    {
        path: '/platform/gateways-card-connect',
        name: 'SystemCardConnectList',
        component: () => import('@/views/platform/gateways/CardConnectList.vue'),
        meta: {
            role: '*',
            breadcrumb: 'Gateways - CardConnect', // Label for the breadcrumb
            parent: 'SystemDashboard', // Reference the parent route
        },
        //meta: { requiresAuth: true, role: ['system_admin', 'admin', 'support'] },
    },
    {
        path: '/platform/gateways-card-connect/edit/:gatewayId?',
        name: 'SystemCardConnectEdit',
        component: () => import('@/views/platform/gateways/CardConnectEdit.vue'),
        meta: {
            role: '*',
            breadcrumb: ':name', // Label for the breadcrumb
            parent: 'SystemCardConnectList', // Reference the parent route
        },
        //meta: { requiresAuth: true, role: ['system_admin', 'admin', 'support'] },
    },
    {
        path: '/platform/merchant/:merchantId?',
        name: 'SystemMerchant',
        component: () => import('@/views/platform/merchant/MerchantSettings.vue'),
        meta: {
            role: '*',
            breadcrumb: 'Merchant :merchantName', // Label for the breadcrumb
            parent: 'SystemDashboard', // Reference the parent route
        },
        //meta: { requiresAuth: true, role: ['system_admin', 'admin', 'support'] },
    },
    {
        path: '/platform/merchant/:merchantId/departments/:departmentId?',
        name: 'DepartmentEdit',
        component: () => import('@/views/platform/merchant/departments/DepartmentEdit.vue'),
        meta: {
            role: '*',
            breadcrumb: 'Department :departmentName', // Label for the breadcrumb
            parent: 'SystemMerchant', // Reference the parent route
        },
        //meta: { requiresAuth: true, role: ['system_admin', 'admin', 'support'] },
    },
    {
        path: '/platform/merchant/:merchantId/departments/:departmentId/payment-gateways/card-connect',
        name: 'CardConnectEdit',
        component: () => import('@/views/platform/merchant/payment-gateways/CardConnectEdit.vue'),
        meta: {
            role: '*',
            breadcrumb: 'Gateway CardConnect', // Label for the breadcrumb
            parent: 'DepartmentEdit', // Reference the parent route
        },
        //meta: { requiresAuth: true, role: ['system_admin', 'admin', 'support'] },
    },
    {
        path: '/platform/merchant/:merchantId/departments/:departmentId/payment-gateways/paya',
        name: 'PayaEdit',
        component: () => import('@/views/platform/merchant/payment-gateways/PayaEdit.vue'),
        meta: {
            role: '*',
            breadcrumb: 'Gateway Paya', // Label for the breadcrumb
            parent: 'DepartmentEdit', // Reference the parent route
        },
        //meta: { requiresAuth: true, role: ['system_admin', 'admin', 'support'] },
    },
    {
        path: '/platform/merchant/:merchantId/departments/:departmentId/billing-gateways/tyler',
        name: 'TylerEdit',
        component: () => import('@/views/platform/merchant/billing-gateways/TylerEdit.vue'),
        meta: {
            role: '*',
            breadcrumb: 'Gateway Tyler', // Label for the breadcrumb
            parent: 'DepartmentEdit', // Reference the parent route
        },
        //meta: { requiresAuth: true, role: ['system_admin', 'admin', 'support'] },
    },
    {
        path: '/platform/merchant/:merchantId/departments/:departmentId/billing-gateways/ez-secure-pay',
        name: 'UrlQueryPayEdit',
        component: () => import('@/views/platform/merchant/billing-gateways/UrlQueryPayEdit.vue'),
        meta: {
            role: '*',
            breadcrumb: 'Gateway URL Query String', // Label for the breadcrumb
            parent: 'DepartmentEdit', // Reference the parent route
        },
        //meta: { requiresAuth: true, role: ['system_admin', 'admin', 'support'] },
    },
    {
        path: '/platform/merchant/:merchantId/departments/:departmentId/invoices-gateways/smart-pay',
        name: 'SmartPayEdit',
        component: () => import('@/views/platform/merchant/invoices-gateways/SmartPayEdit.vue'),
        meta: {
            role: '*',
            breadcrumb: 'Gateway SmartPay', // Label for the breadcrumb
            parent: 'DepartmentEdit', // Reference the parent route
        },
        //meta: { requiresAuth: true, role: ['system_admin', 'admin', 'support'] },
    },
    {
        path: '/platform/merchant/:merchantId/departments/:departmentId/hosted-payments',
        name: 'HostedPaymentsDepartmentEdit',
        component: () => import('@/views/platform/merchant/hosted-payments/HostedPaymentsDepartmentEdit.vue'),
        meta: {
            role: '*',
            breadcrumb: 'Hosted Payments', // Label for the breadcrumb
            parent: 'DepartmentEdit', // Reference the parent route
        },
        //meta: { requiresAuth: true, role: ['system_admin', 'admin', 'support'] },
    },
    {
        path: '/platform/merchant/:merchantId/departments/:departmentId/sub-departments/form',
        name: 'SubDepartmentsEdit',
        component: () => import('@/views/platform/merchant/departments/SubDepartmentsEdit.vue'),
        meta: {
            role: '*',
            breadcrumb: 'Sub Departments', // Label for the breadcrumb
            parent: 'DepartmentEdit', // Reference the parent route
        },
        //meta: { requiresAuth: true, role: ['system_admin', 'admin', 'support'] },
    },
];