/**
 * Ponto de entrada do SPA Vue 3.
 *
 * Vue 3 (Composition API) + Vue Router + Pinia, consumindo a API Laravel.
 */
import { createPinia } from 'pinia';
import { createApp } from 'vue';
import App from '@/App.vue';
import { initializeTheme } from '@/composables/useAppearance';
import { createAppRouter } from '@/router';

initializeTheme();

const app = createApp(App);

app.use(createPinia());
app.use(createAppRouter());

// Erros não tratados: registra no console sem vazar para o usuário.
app.config.errorHandler = (error, _instance, info) => {
    console.error('[app]', info, error);
};

app.mount('#app');
