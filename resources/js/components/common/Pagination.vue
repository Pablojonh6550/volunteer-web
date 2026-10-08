<script setup lang="ts">
import { ChevronLeftIcon, ChevronRightIcon } from '@lucide/vue';
import { Button } from '@/components/ui/button';

const props = defineProps<{
    currentPage: number;
    lastPage: number;
    total: number;
}>();
const emit = defineEmits<{ change: [page: number] }>();

const go = (page: number) => {
    if (page >= 1 && page <= props.lastPage) {
        emit('change', page);
    }
};
</script>

<template>
    <nav
        v-if="lastPage > 1"
        aria-label="Paginação"
        class="mt-6 flex items-center justify-between gap-4"
    >
        <p class="text-muted-foreground text-sm">
            Página {{ currentPage }} de {{ lastPage }} ·
            {{ total }} resultado(s)
        </p>
        <div class="flex gap-2">
            <Button
                variant="outline"
                size="sm"
                :disabled="currentPage <= 1"
                @click="go(currentPage - 1)"
            >
                <ChevronLeftIcon aria-hidden="true" /> Anterior
            </Button>
            <Button
                variant="outline"
                size="sm"
                :disabled="currentPage >= lastPage"
                @click="go(currentPage + 1)"
            >
                Próxima <ChevronRightIcon aria-hidden="true" />
            </Button>
        </div>
    </nav>
</template>
