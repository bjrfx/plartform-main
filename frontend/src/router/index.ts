// index.ts
import {createRouter, createWebHistory} from 'vue-router';
import {routes} from '@/router/routes';
import {useAuthStore} from '@/stores/auth';

// Extend Vue Router meta typing
declare module 'vue-router' {
    interface RouteMeta {
        role?: string | string[]; // Role can be a string, array of strings, or undefined
        requiresAuth?: boolean;
        breadcrumb?: string; // Breadcrumb label
        parent?: string; // Name of the parent route
    }
}


const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Helper function to determine if the domain is a subdomain
/*
function isSubdomain() {
    const hostname = window.location.hostname; // e.g., "sub.example.com" or "example.com"
    const parts = hostname.split('.'); // Split by dots (e.g., ["sub", "example", "com"])

    // Check if "www" is the first part and treat it as the main domain
    if (parts.length > 2 && parts[0] === 'www') {
        return false;
    }
    return parts.length > 2; // If more than 2 parts, it's a subdomain
}

 */

// Navigation Guard
router.beforeEach((to, _from, next) => {
    /*
    if (to.path === '/' && !isSubdomain()) {
        return next({name: 'AdminWelcomePage'}); // Redirect to AdminWelcomePage for main domains
    }
    */

    const authStore = useAuthStore();
    const userRole = authStore.getUserRoll() ?? 'guest'; // Default role is 'guest' if not logged in.
    const isAuth = authStore.isAuth();

    const requiresAuth = "requiresAuth" in to.meta && to.meta.requiresAuth;
    if (requiresAuth && !isAuth) {
        /*
        if (
            userRole === 'system_admin' ||
            userRole === 'admin' ||
            userRole === 'support'
        ) {
            return next({name: 'AdminWelcomePage'});
        }
                 */
        return next({name: 'WelcomePage'});
    }

    if (to.meta.role && to.meta.role !== '*' && !to.meta.role.includes(userRole)) {
        // Role not authorized for this route
        if (
            userRole === 'system_admin' ||
            userRole === 'admin' ||
            userRole === 'support'
        ) {
            // return next({name: 'AdminWelcomePage'});
        }
        return next({name: 'WelcomePage'});
    }

    next(); // Proceed to the route
});

export default router;