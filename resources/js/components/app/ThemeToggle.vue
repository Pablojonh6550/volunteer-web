<script setup lang="ts">
/**
 * Switch claro/escuro. Disponível em todos os layouts, inclusive deslogado;
 * a preferência fica no navegador (ver useAppearance).
 */
import { MoonIcon, SunIcon } from '@lucide/vue';
import { computed } from 'vue';
import { useAppearance } from '@/composables/useAppearance';

const { resolvedAppearance, updateAppearance } = useAppearance();

const isDark = computed(() => resolvedAppearance.value === 'dark');

function toggle() {
    updateAppearance(isDark.value ? 'light' : 'dark');
}
</script>

<template>
    <button
        type="button"
        role="switch"
        :aria-checked="isDark"
        aria-label="Modo escuro"
        :title="isDark ? 'Mudar para o tema claro' : 'Mudar para o tema escuro'"
        class="bg-muted focus-visible:ring-ring relative inline-flex h-8 w-14 shrink-0 cursor-pointer items-center rounded-full border p-0.5 transition-colors outline-none focus-visible:ring-2 focus-visible:ring-offset-2"
        @click="toggle"
    >
        <span
            class="bg-primary text-primary-foreground flex size-6 items-center justify-center rounded-full shadow-sm transition-transform"
            :class="isDark ? 'translate-x-6' : 'translate-x-0'"
        >
            <MoonIcon v-if="isDark" class="size-3.5" aria-hidden="true" />
            <SunIcon v-else class="size-3.5" aria-hidden="true" />
        </span>
    </button>
</template>
