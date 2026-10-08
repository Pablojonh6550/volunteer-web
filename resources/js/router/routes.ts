/**
 * Mapa de rotas do SPA.
 *
 * Todas as páginas são carregadas sob demanda (`() => import(...)`), então
 * cada tela vira um chunk separado e o carregamento inicial fica leve.
 *
 * meta:
 *  - layout:       'public' | 'auth' | 'app'
 *  - requiresAuth: exige sessão
 *  - guestOnly:    apenas visitantes
 *  - role:         'organization' | 'volunteer'
 *  - title:        título da aba e anúncio para leitores de tela
 */
import type { RouteRecordRaw } from 'vue-router';

export const routes: RouteRecordRaw[] = [
    // Públicas
    {
        path: '/',
        name: 'home',
        component: () => import('@/pages/public/HomePage.vue'),
        meta: { title: 'Início', layout: 'public' },
    },
    {
        path: '/como-funciona',
        name: 'how-it-works',
        component: () => import('@/pages/public/HowItWorksPage.vue'),
        meta: { title: 'Como funciona', layout: 'public' },
    },
    {
        path: '/entrar',
        name: 'login',
        component: () => import('@/pages/auth/LoginPage.vue'),
        meta: { title: 'Entrar', layout: 'auth', guestOnly: true },
    },
    {
        path: '/cadastro',
        name: 'register',
        component: () => import('@/pages/auth/RegisterPage.vue'),
        meta: { title: 'Cadastro', layout: 'auth', guestOnly: true },
    },

    // Voluntário
    {
        path: '/voluntario',
        meta: { requiresAuth: true, role: 'volunteer', layout: 'app' },
        children: [
            {
                path: '',
                name: 'volunteer.dashboard',
                component: () => import('@/pages/volunteer/DashboardPage.vue'),
                meta: { title: 'Painel' },
            },
            {
                path: 'perfil',
                name: 'volunteer.profile',
                component: () => import('@/pages/volunteer/ProfilePage.vue'),
                meta: { title: 'Meu perfil' },
            },
            {
                path: 'mapa',
                name: 'volunteer.map',
                component: () =>
                    import('@/pages/volunteer/OrganizationsMapPage.vue'),
                meta: { title: 'Mapa de ONGs' },
            },
            {
                path: 'ongs/:id(\\d+)',
                name: 'volunteer.organization',
                component: () =>
                    import('@/pages/volunteer/OrganizationDetailsPage.vue'),
                props: (route) => ({ id: Number(route.params.id) }),
                meta: { title: 'ONG' },
            },
            {
                path: 'solicitacoes',
                name: 'volunteer.requests',
                component: () =>
                    import('@/pages/volunteer/RequestSearchPage.vue'),
                meta: { title: 'Pesquisar solicitações' },
            },
            {
                path: 'solicitacoes/:id(\\d+)',
                name: 'volunteer.request',
                component: () =>
                    import('@/pages/volunteer/RequestDetailsPage.vue'),
                props: (route) => ({ id: Number(route.params.id) }),
                meta: { title: 'Detalhes da solicitação' },
            },
            {
                path: 'atividades',
                name: 'volunteer.activities',
                component: () => import('@/pages/volunteer/ActivitiesPage.vue'),
                meta: { title: 'Minhas atividades' },
            },
            {
                path: 'configuracoes',
                name: 'volunteer.settings',
                component: () => import('@/pages/shared/SettingsPage.vue'),
                meta: { title: 'Configurações' },
            },
        ],
    },

    // ONG
    {
        path: '/ong',
        meta: { requiresAuth: true, role: 'organization', layout: 'app' },
        children: [
            {
                path: '',
                name: 'organization.dashboard',
                component: () =>
                    import('@/pages/organization/DashboardPage.vue'),
                meta: { title: 'Painel' },
            },
            {
                path: 'perfil',
                name: 'organization.profile',
                component: () => import('@/pages/organization/ProfilePage.vue'),
                meta: { title: 'Perfil da ONG' },
            },
            {
                path: 'solicitacoes',
                name: 'organization.requests',
                component: () =>
                    import('@/pages/organization/RequestListPage.vue'),
                meta: { title: 'Minhas solicitações' },
            },
            {
                path: 'solicitacoes/nova',
                name: 'organization.requests.create',
                component: () =>
                    import('@/pages/organization/RequestFormPage.vue'),
                meta: { title: 'Nova solicitação' },
            },
            {
                path: 'solicitacoes/:id(\\d+)',
                name: 'organization.request',
                component: () =>
                    import('@/pages/organization/RequestDetailsPage.vue'),
                props: (route) => ({ id: Number(route.params.id) }),
                meta: { title: 'Detalhes da solicitação' },
            },
            {
                path: 'solicitacoes/:id(\\d+)/editar',
                name: 'organization.requests.edit',
                component: () =>
                    import('@/pages/organization/RequestFormPage.vue'),
                props: (route) => ({ id: Number(route.params.id) }),
                meta: { title: 'Editar solicitação' },
            },
            {
                path: 'solicitacoes/:id(\\d+)/voluntarios',
                name: 'organization.request.volunteers',
                component: () =>
                    import('@/pages/organization/RequestVolunteersPage.vue'),
                props: (route) => ({ id: Number(route.params.id) }),
                meta: { title: 'Voluntários interessados' },
            },
            {
                path: 'configuracoes',
                name: 'organization.settings',
                component: () => import('@/pages/shared/SettingsPage.vue'),
                meta: { title: 'Configurações' },
            },
        ],
    },

    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: () => import('@/pages/public/NotFoundPage.vue'),
        meta: { title: 'Página não encontrada', layout: 'public' },
    },
];
