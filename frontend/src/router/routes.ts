// routes.ts
import {RouteRecordRaw} from 'vue-router';
import {platformRoutes} from './platformRoutes';
import {adminRoutes} from './adminRoutes';
import {userRoutes} from './userRoutes';
import {publicRoutes} from './publicRoutes';

export const routes: Array<RouteRecordRaw> = [
    ...publicRoutes,
    ...platformRoutes,
    ...adminRoutes,
    ...userRoutes,
];
