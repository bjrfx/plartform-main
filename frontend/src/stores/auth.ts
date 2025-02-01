import {defineStore} from 'pinia';
import {post, get} from '@/services/api';
import {useSiteDataStore} from "@/stores/siteData";
import {UserRole} from '@/enums/enums';

const MAX_INACTIVE_MINUTES: number = 30;

export interface User {
    id: string;
    name: string;
    email: string;
    role: UserRole;
}

export interface RegisterUserData {
    first_name: string;
    middle_name: string | null;
    last_name: string;
    email: string;
    phone_country_code: string;
    phone: string;
    street: string;
    city: string;
    state: string;
    zip_code: string;
    password: string;
    password_confirmation: string;
    is_ebilling_enabled: string;
}

// Declare activityTimeout at the top of the file
let activityTimeout: ReturnType<typeof setTimeout>;
export const useAuthStore = defineStore('auth', {
    state: () => ({
        token: localStorage.getItem('token') || null, // Initialize token from localStorage
        errors: [] as string[],
        success: true as boolean,
        lastActiveTime: Date.now(), // Track the last active time
    }),
    actions: {
        async login(email: string, password: string): Promise<void> {
            this.errors = [];
            try {
                const response = await post('/login', {email, password}) as { access_token: string; user: User };

                this.token = response.access_token;
                localStorage.setItem('token', this.token);

                if ("user" in response) {
                    const siteDataStore = useSiteDataStore();
                    siteDataStore.setUser(response.user);
                }

                this.startActivityTimeout(); // Start tracking inactivity
            } catch (err: any) {
                this.errors = err.message || ['Login failed'];
            }
        },
        async fetchToken(token: string): Promise<any> {
            this.errors = [];
            try {
                const response = await get(`/token/${token}`);

                this.token = response?.access_token;
                if (this.token) {
                    localStorage.setItem('token', this.token);
                    return true;
                }

                return false;

            } catch (err: any) {
                this.errors = err.message || ['Token failed'];
            }
        },
        async logout(): Promise<void> {
            this.errors = [];
            try {
                await get('/logout');
            } catch (err: any) {
                this.errors = err.message;
            }
            this.clearUserData();
            this.stopActivityTimeout(); // Stop tracking inactivity
        },
        async register(userData: RegisterUserData): Promise<any> {
            this.errors = []; // Clear errors
            try {
                const response = await post('/register', userData);
                this.success = response.data.success;
                return response.data;
            } catch (err: any) {
                this.errors = err.message;
            }
        },
        async passwordRules() {
            try {
                return await get('/password-rules');
            } catch (err: any) {
                this.errors = err.message;
            }
        },
        clearUserData(): void {
            this.token = null;
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            this.stopActivityTimeout();
        },
        isAuth(): boolean {
            return !!this.token;
        },
        getUserRoll(): UserRole | null {
            const siteDataStore = useSiteDataStore();

            // Check if user data exists and has a "role" property
            if (siteDataStore.siteData?.user?.role) {
                return siteDataStore.siteData.user.role as UserRole; // Type assertion for safety
            }

            return null; // Return null if no role is found
        },
        startActivityTimeout(): void {
            this.lastActiveTime = Date.now();
            this.resetActivityTimeout();

            const events: string[] = ['mousemove', 'keydown', 'mousedown', 'touchstart'];

            const reset = () => this.updateLastActiveTime();
            events.forEach((event) => window.addEventListener(event, reset));

            window.addEventListener('visibilitychange', this.handleVisibilityChange);
            window.addEventListener('focus', this.checkInactivityAfterWake);

            this.stopActivityTimeout = () => {
                clearTimeout(activityTimeout);
                events.forEach((event) => window.removeEventListener(event, reset));
                window.removeEventListener('visibilitychange', this.handleVisibilityChange);
                window.removeEventListener('focus', this.checkInactivityAfterWake);
            };
        },
        resetActivityTimeout(): void {
            clearTimeout(activityTimeout);
            activityTimeout = setTimeout(async () => {
                try {
                    await this.logout(); // Await the logout function
                } catch (err) {
                    console.error('Error during auto-logout:', err); // Log any potential errors
                }
            }, 30 * 60 * 1000); // 30 minutes
        },
        updateLastActiveTime(): void {
            this.lastActiveTime = Date.now();
            this.resetActivityTimeout();
        },
        checkInactivityAfterWake(): void {
            const currentTime = Date.now();
            const elapsed = currentTime - this.lastActiveTime;
            if (elapsed > (MAX_INACTIVE_MINUTES * 60 * 1000)) { // If more than X minutes in milliseconds
                (async () => {
                    try {
                        await this.logout(); // Await the logout function
                    } catch (err) {
                        console.error('Error during logout after wake:', err);
                    }
                })();
            } else {
                this.resetActivityTimeout();
            }
        },
        handleVisibilityChange(): void {
            if (!document.hidden) {
                this.checkInactivityAfterWake();
            }
        },
        stopActivityTimeout(): void {
            clearTimeout(activityTimeout);
        },
    },
});