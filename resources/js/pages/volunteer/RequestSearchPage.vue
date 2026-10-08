<script setup lang="ts">
/**
 * Pesquisa de solicitações: texto, tipo, categoria, prioridade, status e
 * distância (a partir da localização do perfil ou do navegador).
 */
import { CrosshairIcon, SearchXIcon } from '@lucide/vue';
import { computed, onMounted } from 'vue';
import EmptyState from '@/components/common/EmptyState.vue';
import ErrorState from '@/components/common/ErrorState.vue';
import LoadingState from '@/components/common/LoadingState.vue';
import PageHeader from '@/components/common/PageHeader.vue';
import Pagination from '@/components/common/Pagination.vue';
import RequestCard from '@/components/requests/RequestCard.vue';
import RequestFilters from '@/components/requests/RequestFilters.vue';
import { Button } from '@/components/ui/button';
import { useLocation } from '@/composables/useLocation';
import { useSearch } from '@/composables/useSearch';
import { useAuthStore } from '@/stores/auth';
import type { Coordinates } from '@/types';
import { hasCoordinates } from '@/utils/geo';

const auth = useAuthStore();
const location = useLocation();

const origin = computed<Coordinates | null>(
    () =>
        location.coords.value ??
        (hasCoordinates(auth.volunteer)
            ? {
                  latitude: auth.volunteer.latitude,
                  longitude: auth.volunteer.longitude,
              }
            : null),
);

const {
    filters,
    results,
    meta,
    loading,
    error,
    search,
    goToPage,
    reset,
    activeFilterCount,
} = useSearch({ origin: () => origin.value });

async function useMyLocation(): Promise<void> {
    if (await location.locate()) {
        filters.sort = 'distance';
        void search();
    }
}

onMounted(search);
</script>

<template>
    <PageHeader
        title="Pesquisar solicitações"
        description="Encontre doações, serviços e voluntariado que combinam com você."
    >
        <template #actions>
            <Button
                v-if="!location.hasLocation.value"
                variant="outline"
                :disabled="location.loading.value"
                @click="useMyLocation"
            >
                <CrosshairIcon aria-hidden="true" /> Usar minha localização
            </Button>
        </template>
    </PageHeader>

    <p
        v-if="location.error.value"
        role="alert"
        class="text-destructive mb-4 text-sm"
    >
        {{ location.error.value }}
    </p>

    <RequestFilters
        :filters="filters"
        :has-origin="origin !== null"
        :active-count="activeFilterCount"
        class="mb-6"
        @reset="reset"
    />

    <div aria-live="polite" class="sr-only">
        <template v-if="!loading && meta"
            >{{ meta.total }} solicitação(ões) encontrada(s)</template
        >
    </div>

    <ErrorState v-if="error" :message="error.message" @retry="search" />
    <LoadingState
        v-else-if="loading && results.length === 0"
        :rows="4"
        label="Buscando solicitações…"
    />
    <EmptyState
        v-else-if="results.length === 0"
        :icon="SearchXIcon"
        title="Nenhuma solicitação encontrada"
        description="Ajuste os filtros ou aumente a distância."
    />
    <template v-else>
        <p class="text-muted-foreground mb-3 text-sm">
            {{ meta?.total }} resultado(s)
        </p>
        <div class="grid gap-4 md:grid-cols-2" :aria-busy="loading">
            <RequestCard
                v-for="r in results"
                :key="r.id"
                :request="r"
                :to="{ name: 'volunteer.request', params: { id: r.id } }"
            />
        </div>
        <Pagination
            v-if="meta"
            :current-page="meta.current_page"
            :last-page="meta.last_page"
            :total="meta.total"
            @change="goToPage"
        />
    </template>
</template>
