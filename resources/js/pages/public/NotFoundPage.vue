<script setup lang="ts">
import { Button } from '@/components/ui/button';
</script>

<template>
    <div
        class="mx-auto flex max-w-md flex-col items-center gap-4 px-4 py-24 text-center"
    >
        <p class="text-primary-strong text-5xl font-bold">404</p>
        <h1 class="text-2xl font-semibold">Página não encontrada</h1>
        <p class="text-muted-foreground">
            O endereço acessado não existe ou foi removido.
        </p>
        <Button as-child
            ><RouterLink to="/">Voltar ao início</RouterLink></Button
        >
    </div>
</template>
