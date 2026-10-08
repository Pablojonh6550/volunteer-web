/**
 * Estado global de autenticação (Pinia).
 *
 * A sessão vive em cookie HttpOnly (Sanctum); aqui guardamos apenas o
 * usuário retornado por GET /api/me para decidir rotas, menus e permissões
 * na interface. Nada sensível é persistido em localStorage.
 */
import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { authService, onUnauthorized } from '@/services';
import type { LoginPayload, RegisterPayload } from '@/services';
import type { Organization, User, Volunteer } from '@/types';

export const useAuthStore = defineStore('auth', () => {
    const user = ref<User | null>(null);
    /** `true` depois da primeira tentativa de carregar o usuário. */
    const initialized = ref(false);
    let pendingFetch: Promise<User | null> | null = null;

    const isAuthenticated = computed(() => user.value !== null);
    const isOrganization = computed(() => user.value?.type === 'organization');
    const isVolunteer = computed(() => user.value?.type === 'volunteer');
    const organization = computed<Organization | null>(
        () => user.value?.organization ?? null,
    );
    const volunteer = computed<Volunteer | null>(
        () => user.value?.volunteer ?? null,
    );

    /** Nome da rota inicial do perfil logado. */
    const homeRouteName = computed(() =>
        isOrganization.value ? 'organization.dashboard' : 'volunteer.dashboard',
    );

    /**
     * Carrega o usuário da sessão uma única vez (chamadas concorrentes
     * compartilham a mesma Promise). Use `force` para recarregar.
     */
    async function fetchUser(force = false): Promise<User | null> {
        if (initialized.value && !force) {
            return user.value;
        }

        pendingFetch ??= authService
            .me()
            .then((me) => (user.value = me))
            .catch(() => (user.value = null))
            .finally(() => {
                initialized.value = true;
                pendingFetch = null;
            });

        return pendingFetch;
    }

    async function login(payload: LoginPayload): Promise<User> {
        user.value = await authService.login(payload);
        initialized.value = true;

        return user.value;
    }

    async function register(payload: RegisterPayload): Promise<User> {
        user.value = await authService.register(payload);
        initialized.value = true;

        return user.value;
    }

    async function logout(): Promise<void> {
        try {
            await authService.logout();
        } finally {
            clear();
        }
    }

    function setUser(value: User): void {
        user.value = value;
    }

    function setOrganization(value: Organization): void {
        if (user.value) {
            user.value = { ...user.value, organization: value };
        }
    }

    function setVolunteer(value: Volunteer): void {
        if (user.value) {
            user.value = { ...user.value, volunteer: value };
        }
    }

    function clear(): void {
        user.value = null;
        initialized.value = true;
    }

    // Sessão expirada em qualquer requisição → limpa o estado local.
    onUnauthorized(clear);

    return {
        user,
        initialized,
        isAuthenticated,
        isOrganization,
        isVolunteer,
        organization,
        volunteer,
        homeRouteName,
        fetchUser,
        login,
        register,
        logout,
        setUser,
        setOrganization,
        setVolunteer,
        clear,
    };
});
