<script setup lang="ts">
import { Skeleton } from '@/components/ui/skeleton';

withDefaults(defineProps<{ rows?: number; label?: string }>(), {
    rows: 3,
    label: 'Carregando…',
});
</script>

<template>
    <div role="status" aria-live="polite" class="grid gap-3">
        <span class="sr-only">{{ label }}</span>
        <Skeleton v-for="n in rows" :key="n" class="h-24 w-full rounded-xl" />
    </div>
</template>
