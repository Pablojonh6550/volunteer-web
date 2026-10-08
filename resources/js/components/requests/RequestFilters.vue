<script setup lang="ts">
/**
 * Filtros da pesquisa de solicitações. Recebe o objeto reativo de filtros
 * de `useSearch` e altera seus campos diretamente (v-model).
 */
import { SearchIcon, XIcon } from '@lucide/vue';
import { computed, onMounted } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { NativeSelect } from '@/components/ui/native-select';
import type { SearchFilters } from '@/composables/useSearch';
import { useCategoriesStore } from '@/stores/categories';
import {
    REQUEST_PRIORITY_LABELS,
    REQUEST_STATUS_LABELS,
    REQUEST_TYPE_LABELS,
    toOptions,
} from '@/types';
import type { RequestType } from '@/types';
import { RADIUS_OPTIONS } from '@/utils/geo';

const props = defineProps<{
    filters: SearchFilters;
    /** Há ponto de referência? Habilita raio e ordenação por distância. */
    hasOrigin?: boolean;
    /** Permite filtrar por qualquer status (visão da ONG). */
    allStatuses?: boolean;
    activeCount?: number;
}>();
defineEmits<{ reset: [] }>();

const categories = useCategoriesStore();
onMounted(() => void categories.load());

const categoryOptions = computed(() =>
    categories
        .forType(props.filters.type as RequestType | '')
        .map((c) => ({ value: String(c.id), label: c.name })),
);

const statusOptions = computed(() =>
    toOptions(REQUEST_STATUS_LABELS).filter(
        (o) => props.allStatuses || ['open', 'in_progress'].includes(o.value),
    ),
);

const sortOptions = computed(() => [
    { value: 'recent', label: 'Mais recentes' },
    { value: 'priority', label: 'Maior prioridade' },
    ...(props.hasOrigin ? [{ value: 'distance', label: 'Mais próximas' }] : []),
]);

const radiusOptions = RADIUS_OPTIONS.map((km) => ({
    value: String(km),
    label: `Até ${km} km`,
}));
</script>

<template>
    <form
        role="search"
        aria-label="Filtrar solicitações"
        class="grid gap-4 rounded-xl border p-4"
        @submit.prevent
    >
        <div class="grid gap-2">
            <Label for="filter-q">Buscar</Label>
            <div class="relative">
                <SearchIcon
                    class="text-muted-foreground absolute top-1/2 left-3 size-4 -translate-y-1/2"
                    aria-hidden="true"
                />
                <Input
                    id="filter-q"
                    v-model="filters.q"
                    type="search"
                    class="pl-9"
                    placeholder="Título ou descrição"
                />
            </div>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="grid gap-2">
                <Label for="filter-type">Tipo</Label>
                <NativeSelect
                    id="filter-type"
                    v-model="filters.type"
                    placeholder="Todos"
                    :options="toOptions(REQUEST_TYPE_LABELS)"
                    @change="filters.category_id = ''"
                />
            </div>
            <div class="grid gap-2">
                <Label for="filter-category">Categoria</Label>
                <NativeSelect
                    id="filter-category"
                    v-model="filters.category_id"
                    placeholder="Todas"
                    :options="categoryOptions"
                />
            </div>
            <div class="grid gap-2">
                <Label for="filter-priority">Prioridade</Label>
                <NativeSelect
                    id="filter-priority"
                    v-model="filters.priority"
                    placeholder="Todas"
                    :options="toOptions(REQUEST_PRIORITY_LABELS)"
                />
            </div>
            <div class="grid gap-2">
                <Label for="filter-status">Status</Label>
                <NativeSelect
                    id="filter-status"
                    v-model="filters.status"
                    :placeholder="
                        allStatuses ? 'Todos' : 'Abertas e em andamento'
                    "
                    :options="statusOptions"
                />
            </div>
            <div v-if="hasOrigin" class="grid gap-2">
                <Label for="filter-radius">Distância</Label>
                <NativeSelect
                    id="filter-radius"
                    v-model="filters.radius"
                    placeholder="Qualquer distância"
                    :options="radiusOptions"
                />
            </div>
            <div class="grid gap-2">
                <Label for="filter-sort">Ordenar por</Label>
                <NativeSelect
                    id="filter-sort"
                    v-model="filters.sort"
                    :options="sortOptions"
                />
            </div>
        </div>
        <div
            v-if="(activeCount ?? 0) > 0 || filters.q"
            class="flex justify-end"
        >
            <Button
                type="button"
                variant="ghost"
                size="sm"
                @click="$emit('reset')"
                ><XIcon aria-hidden="true" /> Limpar filtros</Button
            >
        </div>
    </form>
</template>
