import { createRouter, createWebHistory } from 'vue-router';
import { authGuard, updateDocumentTitle } from './guards';
import { routes } from './routes';

export function createAppRouter() {
    const router = createRouter({
        history: createWebHistory(),
        routes,
        scrollBehavior(to, _from, savedPosition) {
            if (savedPosition) {
                return savedPosition;
            }

            return to.hash ? { el: to.hash } : { top: 0 };
        },
    });

    router.beforeEach(authGuard);
    router.afterEach(updateDocumentTitle);

    return router;
}
