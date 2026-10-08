<script setup lang="ts">
/** Layout das páginas públicas (home, como funciona). */
import AppLogo from '@/components/app/AppLogo.vue';
import SkipLink from '@/components/app/SkipLink.vue';
import { Button } from '@/components/ui/button';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
</script>

<template>
    <SkipLink />
    <div class="flex min-h-svh flex-col">
        <header class="border-b">
            <div
                class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4"
            >
                <RouterLink to="/" aria-label="Página inicial"
                    ><AppLogo
                /></RouterLink>
                <nav
                    aria-label="Navegação pública"
                    class="flex items-center gap-1 sm:gap-2"
                >
                    <Button variant="ghost" as-child
                        ><RouterLink :to="{ name: 'how-it-works' }"
                            >Como funciona</RouterLink
                        ></Button
                    >
                    <template v-if="auth.isAuthenticated">
                        <Button as-child
                            ><RouterLink :to="{ name: auth.homeRouteName }"
                                >Meu painel</RouterLink
                            ></Button
                        >
                    </template>
                    <template v-else>
                        <Button variant="ghost" as-child
                            ><RouterLink :to="{ name: 'login' }"
                                >Entrar</RouterLink
                            ></Button
                        >
                        <Button as-child class="hidden sm:inline-flex"
                            ><RouterLink :to="{ name: 'register' }"
                                >Cadastre-se</RouterLink
                            ></Button
                        >
                    </template>
                </nav>
            </div>
        </header>
        <main id="main-content" tabindex="-1" class="flex-1 outline-none">
            <slot />
        </main>
        <footer class="text-muted-foreground border-t py-6 text-center text-sm">
            Conecta ONGs · Plataforma de conexão entre ONGs e voluntários
        </footer>
    </div>
</template>
