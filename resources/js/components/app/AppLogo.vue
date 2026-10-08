<script setup lang="ts">
import { HandHeartIcon } from '@lucide/vue';

defineProps<{ compact?: boolean }>();
</script>

<template>
    <span class="flex items-center gap-2 font-semibold">
        <span
            class="bg-primary text-primary-foreground flex size-8 items-center justify-center rounded-lg"
        >
            <HandHeartIcon class="size-5" aria-hidden="true" />
        </span>
        <span v-if="!compact">Conecta ONGs</span>
    </span>
</template>
