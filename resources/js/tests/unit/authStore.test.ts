import { createPinia, setActivePinia } from 'pinia';
import { beforeEach, describe, expect, it, vi } from 'vite-plus/test';
import { authService } from '@/services/authService';
import { useAuthStore } from '@/stores/auth';
import type { User } from '@/types';

const organizationUser: User = {
    id: 1,
    name: 'ONG Teste',
    email: 'ong@example.com',
    type: 'organization',
    phone: null,
    avatar_url: null,
    organization: null,
    volunteer: null,
    created_at: null,
};

describe('stores/auth', () => {
    beforeEach(() => setActivePinia(createPinia()));

    it('carrega o usuário uma única vez, mesmo com chamadas concorrentes', async () => {
        const me = vi
            .spyOn(authService, 'me')
            .mockResolvedValue(organizationUser);
        const store = useAuthStore();

        await Promise.all([store.fetchUser(), store.fetchUser()]);
        await store.fetchUser();

        expect(me).toHaveBeenCalledTimes(1);
        expect(store.isAuthenticated).toBe(true);
        expect(store.isOrganization).toBe(true);
        expect(store.homeRouteName).toBe('organization.dashboard');
    });

    it('considera visitante quando /me falha', async () => {
        vi.spyOn(authService, 'me').mockRejectedValue({ status: 401 });
        const store = useAuthStore();

        await store.fetchUser();

        expect(store.isAuthenticated).toBe(false);
        expect(store.initialized).toBe(true);
    });

    it('limpa o estado no logout mesmo se a API falhar', async () => {
        vi.spyOn(authService, 'logout').mockRejectedValue(new Error('offline'));
        const store = useAuthStore();
        store.setUser(organizationUser);

        await expect(store.logout()).rejects.toThrow();
        expect(store.user).toBeNull();
    });
});
