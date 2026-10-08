import type { ApiResource, User } from '@/types';
import { http } from './http';

export interface AccountPayload {
    name: string;
    email: string;
    phone?: string | null;
}

export interface PasswordPayload {
    current_password: string;
    password: string;
    password_confirmation: string;
}

export const accountService = {
    async update(payload: AccountPayload): Promise<User> {
        const { data } = await http.put<ApiResource<User>>('/me', payload);

        return data.data;
    },

    async updatePassword(payload: PasswordPayload): Promise<void> {
        await http.put('/me/password', payload);
    },

    async uploadAvatar(file: File): Promise<User> {
        const form = new FormData();
        form.append('avatar', file);
        const { data } = await http.post<ApiResource<User>>('/me/avatar', form);

        return data.data;
    },

    async removeAvatar(): Promise<User> {
        const { data } = await http.delete<ApiResource<User>>('/me/avatar');

        return data.data;
    },

    async deleteAccount(password: string): Promise<void> {
        await http.delete('/me', { data: { password } });
    },
};
