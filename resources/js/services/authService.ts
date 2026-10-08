import type { ApiResource, User, UserType } from '@/types';
import { ensureCsrfCookie, http } from './http';

export interface LoginPayload {
    email: string;
    password: string;
    remember?: boolean;
}

export interface RegisterPayload {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
    type: UserType;
    phone?: string;
    organization_name?: string;
    terms: boolean;
}

export const authService = {
    async login(payload: LoginPayload): Promise<User> {
        await ensureCsrfCookie();
        const { data } = await http.post<ApiResource<User>>(
            '/auth/login',
            payload,
        );

        return data.data;
    },

    async register(payload: RegisterPayload): Promise<User> {
        await ensureCsrfCookie();
        const { data } = await http.post<ApiResource<User>>(
            '/auth/register',
            payload,
        );

        return data.data;
    },

    async logout(): Promise<void> {
        await http.post('/auth/logout');
    },

    async me(): Promise<User> {
        const { data } = await http.get<ApiResource<User>>('/me');

        return data.data;
    },
};
