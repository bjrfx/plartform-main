import {RouteRecordRaw} from 'vue-router';

export const userRoutes: Array<RouteRecordRaw> = [
    {
        path: '/shop',
        name: 'Shop',
        component: () => import('@/components/Shop.vue'),
        meta: {role: '*'}, // Accessible by all roles
    },
];