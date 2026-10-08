<script setup lang="ts">
/**
 * Componente raiz: escolhe o layout conforme `route.meta.layout` e monta
 * recursos globais (toasts, anúncio de rota para leitores de tela).
 */
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import RouteAnnouncer from '@/components/app/RouteAnnouncer.vue';
import { Toaster } from '@/components/ui/sonner';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';

const route = useRoute();

const layouts = { app: AppLayout, auth: AuthLayout, public: PublicLayout };
const layout = computed(() => layouts[route.meta.layout ?? 'public']);
</script>

<template>
    <component :is="layout">
        <RouterView v-slot="{ Component }">
            <component :is="Component" />
        </RouterView>
    </component>
    <RouteAnnouncer />
    <Toaster rich-colors close-button position="top-right" />
</template>
