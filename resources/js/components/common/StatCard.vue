<script setup lang="ts">
import type { Component } from 'vue';
import { Card, CardContent } from '@/components/ui/card';

defineProps<{
    label: string;
    value: number | string | null;
    icon?: Component;
    hint?: string;
}>();
</script>

<template>
    <Card class="gap-0 py-0">
        <CardContent class="flex items-center gap-4 p-5">
            <div
                v-if="icon"
                class="bg-primary/10 text-primary-strong rounded-lg p-2.5"
            >
                <component :is="icon" class="size-5" aria-hidden="true" />
            </div>
            <div>
                <p class="text-muted-foreground text-sm">{{ label }}</p>
                <p class="text-2xl font-semibold tabular-nums">
                    {{ value ?? '—' }}
                </p>
                <p v-if="hint" class="text-muted-foreground text-xs">
                    {{ hint }}
                </p>
            </div>
        </CardContent>
    </Card>
</template>
