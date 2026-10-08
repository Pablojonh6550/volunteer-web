import 'vue-router';
import type { UserType } from './enums';

declare module 'vite/client' {
    interface ImportMetaEnv {
        readonly VITE_APP_NAME: string;
        [key: string]: string | boolean | undefined;
    }
}

/**
 * Metadados das rotas usados pelos guards (router/guards.ts) e pelo layout.
 */
declare module 'vue-router' {
    interface RouteMeta {
        /** Título da página (document.title e anúncio para leitores de tela). */
        title?: string;
        /** Exige usuário autenticado. */
        requiresAuth?: boolean;
        /** Somente visitantes (ex.: login). */
        guestOnly?: boolean;
        /** Restringe a um perfil. */
        role?: UserType;
        /** Layout a ser usado: public (padrão), auth ou app. */
        layout?: 'public' | 'auth' | 'app';
    }
}
