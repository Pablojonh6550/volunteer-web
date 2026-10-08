<script setup lang="ts">
/** Seleção múltipla de áreas de interesse, agrupadas por tipo. */
import { onMounted } from 'vue';
import { useCategoriesStore } from '@/stores/categories';
import { REQUEST_TYPE_LABELS } from '@/types';
import type { RequestType } from '@/types';

const selected = defineModel<number[]>({ default: () => [] });
defineProps<{ error?: string }>();

const categories = useCategoriesStore();
onMounted(() => void categories.load());

const types = Object.keys(REQUEST_TYPE_LABELS) as RequestType[];
</script>

<template>
    <fieldset
        class="grid gap-4"
        :aria-describedby="error ? 'categories-error' : undefined"
    >
        <legend class="text-sm font-medium">Áreas de interesse</legend>
        <div class="grid gap-4 sm:grid-cols-3">
            <div
                v-for="type in types"
                :key="type"
                class="grid content-start gap-2"
            >
                <p
                    class="text-muted-foreground text-xs font-medium tracking-wide uppercase"
                >
                    {{ REQUEST_TYPE_LABELS[type] }}
                </p>
                <label
                    v-for="category in categories.byType[type]"
                    :key="category.id"
                    class="flex items-center gap-2 text-sm"
                >
                    <input
                        v-model="selected"
                        type="checkbox"
                        :value="category.id"
                        class="accent-primary size-4"
                    />
                    {{ category.name }}
                </label>
            </div>
        </div>
        <p
            v-if="error"
            id="categories-error"
            class="text-destructive text-sm"
            role="alert"
        >
            {{ error }}
        </p>
    </fieldset>
</template>
