<script setup lang="ts">
/**
 * Acessibilidade em SPA: a troca de rota não recarrega a página, então
 * leitores de tela não percebem a navegação. Este componente anuncia o
 * título da nova página (aria-live) e move o foco para o conteúdo principal.
 */
import { nextTick, ref } from 'vue';
import { useRouter } from 'vue-router';

const message = ref('');
const router = useRouter();
let first = true;

router.afterEach(async (to) => {
    if (first) {
        first = false;

        return;
    }

    message.value = '';
    await nextTick();
    message.value = `${to.meta.title ?? 'Página'} carregada`;
    document.getElementById('main-content')?.focus({ preventScroll: true });
});
</script>

<template>
    <div class="sr-only" aria-live="polite" aria-atomic="true">
        {{ message }}
    </div>
</template>
