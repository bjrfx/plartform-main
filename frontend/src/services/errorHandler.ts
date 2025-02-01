// src/services/errorHandler.ts
export interface ApiError {
    code: number;
    errors: Record<string, any>;
    isSuccess: boolean;
}

// Normalize success responses for non-GET methods
export const normalizeSuccess = (response: any): ApiError | null => {
    if (response && response.config?.method === 'get') {
        return null;
    }
    if (response) {
        return {
            code: response.status,
            errors: {},
            isSuccess: true,
        };
    }
    return {
        code: 500,
        errors: {message: 'Unexpected error'},
        isSuccess: false,
    };
};

// Normalize errors
export const normalizeError = (error: any): ApiError => {
    if (error.response && error.config?.method !== 'get') {
        return {
            code: error.response.status,
            //errors: error.response.data.errors || {message: error.response.data.message || 'Unknown error'},
            errors: error.response.data || 'Unknown error',
            isSuccess: false,
        };
    }
    return {
        code: 500,
        errors: {message: 'Network or server error'},
        isSuccess: false,
    };
};