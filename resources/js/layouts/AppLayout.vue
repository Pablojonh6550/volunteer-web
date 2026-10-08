<script setup lang="ts">
/** Layout do ambiente logado (ONG e voluntário): sidebar + conteúdo. */
import AppSidebar from '@/components/app/AppSidebar.vue';
import SkipLink from '@/components/app/SkipLink.vue';
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
            <header
                class="flex h-14 items-center gap-2 border-b px-4 md:hidden"
            >
                <SidebarTrigger aria-label="Abrir menu" />
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
