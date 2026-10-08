/**
 * Guards de navegação: autenticação e autorização por tipo de usuário.
 *
 * A autorização real acontece na API (Policies); estes guards apenas evitam
 * que o usuário navegue para telas que não pertencem ao seu perfil.
 */
import type {
    NavigationGuardWithThis,
    RouteLocationNormalized,
} from 'vue-router';
import { useAuthStore } from '@/stores/auth';

export const authGuard: NavigationGuardWithThis<undefined> = async (
    to: RouteLocationNormalized,
) => {
    const auth = useAuthStore();
    await auth.fetchUser();

    if (to.meta.requiresAuth && !auth.isAuthenticated) {
        return { name: 'login', query: { redirect: to.fullPath } };
    }

    if (to.meta.guestOnly && auth.isAuthenticated) {
        return { name: auth.homeRouteName };
    }

    if (to.meta.role && auth.user?.type !== to.meta.role) {
        return { name: auth.homeRouteName };
    }

    return true;
};

const appName = import.meta.env.VITE_APP_NAME || 'Volunteer Web';

/** Atualiza o título da aba a cada navegação. */
export function updateDocumentTitle(to: RouteLocationNormalized): void {
    document.title = to.meta.title ? `${to.meta.title} · ${appName}` : appName;
}
