import { createPinia, setActivePinia } from 'pinia';
import { beforeEach, describe, expect, it, vi } from 'vite-plus/test';
import type { RouteLocationNormalized } from 'vue-router';
import { authGuard } from '@/router/guards';
import { authService } from '@/services/authService';
import type { User } from '@/types';

const volunteer: User = {
    id: 2,
    name: 'Voluntária',
    email: 'v@example.com',
    type: 'volunteer',
    phone: null,
    avatar_url: null,
    created_at: null,
};

function route(
    meta: RouteLocationNormalized['meta'],
    fullPath = '/x',
): RouteLocationNormalized {
    return { meta, fullPath } as RouteLocationNormalized;
}

// O guard é chamado pelo router com (to, from, next); aqui só `to` importa.
const run = (to: RouteLocationNormalized) =>
    (authGuard as (to: RouteLocationNormalized) => unknown)(to);

describe('router/guards', () => {
    beforeEach(() => setActivePinia(createPinia()));

    it('redireciona visitantes para o login preservando o destino', async () => {
        vi.spyOn(authService, 'me').mockRejectedValue({ status: 401 });

        await expect(
            run(route({ requiresAuth: true }, '/voluntario/atividades')),
        ).resolves.toEqual({
            name: 'login',
            query: { redirect: '/voluntario/atividades' },
        });
    });

    it('impede voluntário de acessar rotas de ONG', async () => {
        vi.spyOn(authService, 'me').mockResolvedValue(volunteer);

        await expect(
            run(route({ requiresAuth: true, role: 'organization' })),
        ).resolves.toEqual({ name: 'volunteer.dashboard' });
    });

    it('envia usuário logado para o painel ao abrir o login', async () => {
        vi.spyOn(authService, 'me').mockResolvedValue(volunteer);

        await expect(run(route({ guestOnly: true }))).resolves.toEqual({
            name: 'volunteer.dashboard',
        });
    });

    it('libera rota do próprio perfil', async () => {
        vi.spyOn(authService, 'me').mockResolvedValue(volunteer);

        await expect(
            run(route({ requiresAuth: true, role: 'volunteer' })),
        ).resolves.toBe(true);
    });
});
