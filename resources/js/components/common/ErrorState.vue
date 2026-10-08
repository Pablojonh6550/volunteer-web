<script setup lang="ts">
import { AlertTriangleIcon } from '@lucide/vue';
import { Button } from '@/components/ui/button';

defineProps<{ message: string }>();
defineEmits<{ retry: [] }>();
</script>

<template>
    <div
        role="alert"
        class="border-destructive/40 bg-destructive/5 flex flex-col items-center gap-3 rounded-xl border p-8 text-center"
    >
        <AlertTriangleIcon class="text-destructive size-8" aria-hidden="true" />
        <p class="text-sm">{{ message }}</p>
        <Button variant="outline" size="sm" @click="$emit('retry')"
            >Tentar novamente</Button
        >
    </div>
</template>
