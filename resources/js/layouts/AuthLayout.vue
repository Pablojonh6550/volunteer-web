<script setup lang="ts">
/** Layout das telas de login e cadastro. */
import AppLogo from '@/components/app/AppLogo.vue';
import SkipLink from '@/components/app/SkipLink.vue';
import ThemeToggle from '@/components/app/ThemeToggle.vue';
</script>

<template>
    <SkipLink />
    <div
        class="bg-muted/40 relative flex min-h-svh flex-col items-center justify-center gap-6 p-6"
    >
        <div class="absolute top-4 right-4"><ThemeToggle /></div>
        <RouterLink to="/" aria-label="Página inicial"><AppLogo /></RouterLink>
        <main
            id="main-content"
            tabindex="-1"
            class="w-full max-w-md outline-none"
        >
            <slot />
        </main>
    </div>
</template>
