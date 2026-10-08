/**
 * Autenticação para componentes: expõe a store e ações com navegação.
 */
import { storeToRefs } from 'pinia';
import { useRoute, useRouter } from 'vue-router';
import type { LoginPayload, RegisterPayload } from '@/services';
import { useAuthStore } from '@/stores/auth';
import { useNotification } from './useNotification';

export function useAuth() {
    const store = useAuthStore();
    const router = useRouter();
    const route = useRoute();
    const notify = useNotification();
    const refs = storeToRefs(store);

    /** Redireciona para `?redirect=` (se for rota interna) ou para o dashboard do perfil. */
    async function goHome(): Promise<void> {
        const redirect = route.query.redirect;

        if (
            typeof redirect === 'string' &&
            redirect.startsWith('/') &&
            !redirect.startsWith('//')
        ) {
            await router.push(redirect);

            return;
        }

        await router.push({ name: store.homeRouteName });
    }

    async function login(payload: LoginPayload): Promise<void> {
        await store.login(payload);
        await goHome();
    }

    async function register(payload: RegisterPayload): Promise<void> {
        const user = await store.register(payload);
        notify.success(
            `Bem-vindo(a), ${user.name}!`,
            'Complete seu perfil para começar.',
        );
        await router.push({
            name:
                user.type === 'organization'
                    ? 'organization.profile'
                    : 'volunteer.profile',
        });
    }

    async function logout(): Promise<void> {
        await store.logout();
        await router.push({ name: 'login' });
    }

    return { ...refs, login, register, logout, fetchUser: store.fetchUser };
}
