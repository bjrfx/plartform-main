import type {AxiosInstance, InternalAxiosRequestConfig} from 'axios';
import {useAuthStore} from '@/stores/auth';
import {normalizeError, normalizeSuccess, ApiError} from './errorHandler';
import {ref} from 'vue';

// Reactive error state that can be null or an error object
const apiError = ref<ApiError | null>(null);

export const setupRequestInterceptor = (api: AxiosInstance) => {
    api.interceptors.request.use(
        (config: InternalAxiosRequestConfig) => {
            const authStore = useAuthStore();
            if (authStore.token) {
                config.headers.set('Authorization', `Bearer ${authStore.token}`);
            }
            apiError.value = null;  // Reset error before each request

            return config;
        },
        (error) => Promise.reject(error)
    );
};

export const setupResponseInterceptor = (api: AxiosInstance) => {
    api.interceptors.response.use(
        (response) => {
            apiError.value = normalizeSuccess(response);  // Capture success state for non-GET
            return response;
        },
        (error) => {
            apiError.value = normalizeError(error);  // Assign normalized error object
            return Promise.reject(apiError.value);   // Pass error downstream
        }
    );
};

// Return the ref directly to preserve reactivity
export const getApiError = () => apiError.value;