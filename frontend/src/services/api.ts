// noinspection JSUnusedGlobalSymbols

// src/services/api.ts
import axios, {AxiosRequestConfig, AxiosResponse} from 'axios';
import {setupRequestInterceptor, setupResponseInterceptor, getApiError} from './interceptors';
import {ref, inject, App} from "vue";

export type {ApiError} from './errorHandler';

export {getApiError};  // Re-export for convenience

// Reactive loading state
const isSiteLoading = ref(false);

const api = axios.create({
    baseURL: `${window.location.origin}/api`,
    headers: {
        'Content-Type': 'application/json',
    },
});

// Attach interceptors
setupRequestInterceptor(api);
setupResponseInterceptor(api);

// Helper to manage loading state
const setLoadingState = (loading: boolean): void => {
    isSiteLoading.value = loading;
};

// Export Axios instance and typed methods

// Export Axios instance and typed methods
export const get = <T = Record<string, any>>(url: string, config?: AxiosRequestConfig): Promise<T> => {
    setLoadingState(true);
    return api
        .get<T>(url, config)
        .then((res: AxiosResponse<T>) => res.data)
        .finally(() => setLoadingState(false));
};

export const post = <T = Record<string, any>>(url: string, data?: any, config?: AxiosRequestConfig): Promise<T> => {
    setLoadingState(true);
    return api
        .post<T>(url, data, config)
        .then((res: AxiosResponse<T>) => res.data)
        .finally(() => setLoadingState(false));
};

export const put = <T = Record<string, any>>(url: string, data?: any, config?: AxiosRequestConfig): Promise<T> => {
    setLoadingState(true);
    return api
        .put<T>(url, data, config)
        .then((res: AxiosResponse<T>) => res.data)
        .finally(() => setLoadingState(false));
};

export const del = <T = Record<string, any>>(url: string, config?: AxiosRequestConfig): Promise<T> => {
    setLoadingState(true);
    return api
        .delete<T>(url, config)
        .then((res: AxiosResponse<T>) => res.data)
        .finally(() => setLoadingState(false));
};
/*
export const get = <T = Record<string, any>>(url: string, config?: any): Promise<T> =>
    api.get<T>(url, config).then((res: AxiosResponse<T, any>) => res.data);

export const post = <T = Record<string, any>>(url: string, data?: any, config?: any): Promise<T> =>
    api.post<T>(url, data, config).then((res: AxiosResponse<T, any>) => res.data);

export const put = <T = Record<string, any>>(url: string, data?: any, config?: any): Promise<T> =>
    api.put<T>(url, data, config).then((res: AxiosResponse<T, any>) => res.data);

export const del = <T = Record<string, any>>(url: string, config?: any): Promise<T> =>
    api.delete<T>(url, config).then((res: AxiosResponse<T, any>) => res.data);
*/

export const responseMerge = (target: Record<string, any>, source: Record<string, any>): void => {
    for (const key in source) {
        if (
            source[key] &&
            typeof source[key] === 'object' &&
            !Array.isArray(source[key])
        ) {
            if (!target[key]) target[key] = {};
            responseMerge(target[key], source[key]);  // Recursive merge for nested objects
        } else {
            target[key] = source[key];
        }
    }
};

// Provide function to expose loading state globally
export const provideLoading = (app: App) => {
    app.provide('isSiteLoading', isSiteLoading);
};

// Export state for manual use
export const useLoadingState = () => inject('isSiteLoading', ref(false));
export default api;
