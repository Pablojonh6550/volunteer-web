<script setup lang="ts">
/** Layout do ambiente logado (ONG e voluntário): sidebar + conteúdo. */
import AppSidebar from '@/components/app/AppSidebar.vue';
import SkipLink from '@/components/app/SkipLink.vue';
import ThemeToggle from '@/components/app/ThemeToggle.vue';
import {
    SidebarInset,
    SidebarProvider,
    SidebarTrigger,
} from '@/components/ui/sidebar';
</script>

<template>
    <SkipLink />
    <SidebarProvider>
        <AppSidebar />
        <SidebarInset>
            <header class="flex h-14 items-center gap-2 border-b px-4">
                <SidebarTrigger aria-label="Abrir menu" class="md:hidden" />
                <ThemeToggle class="ml-auto" />
            </header>
            <main
                id="main-content"
                tabindex="-1"
                class="mx-auto w-full max-w-6xl flex-1 p-4 outline-none md:p-8"
            >
                <slot />
            </main>
        </SidebarInset>
    </SidebarProvider>
</template>
