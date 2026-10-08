<script setup lang="ts">
import { ClipboardListIcon, PlusIcon } from '@lucide/vue';
import { onMounted } from 'vue';
import EmptyState from '@/components/common/EmptyState.vue';
import ErrorState from '@/components/common/ErrorState.vue';
import LoadingState from '@/components/common/LoadingState.vue';
import PageHeader from '@/components/common/PageHeader.vue';
import Pagination from '@/components/common/Pagination.vue';
import RequestCard from '@/components/requests/RequestCard.vue';
import RequestFilters from '@/components/requests/RequestFilters.vue';
import { Button } from '@/components/ui/button';
import { useSearch } from '@/composables/useSearch';

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
} = useSearch({ base: { mine: true } });

onMounted(search);
</script>

<template>
    <PageHeader
        title="Minhas solicitações"
        description="Gerencie as solicitações publicadas pela sua ONG."
    >
        <template #actions>
            <Button as-child
                ><RouterLink :to="{ name: 'organization.requests.create' }"
                    ><PlusIcon aria-hidden="true" /> Nova
                    solicitação</RouterLink
                ></Button
            >
        </template>
    </PageHeader>

    <RequestFilters
        :filters="filters"
        all-statuses
        :active-count="activeFilterCount"
        class="mb-6"
        @reset="reset"
    />

    <ErrorState v-if="error" :message="error.message" @retry="search" />
    <LoadingState v-else-if="loading && results.length === 0" />
    <EmptyState
        v-else-if="results.length === 0"
        :icon="ClipboardListIcon"
        title="Nenhuma solicitação encontrada"
        description="Publique a primeira solicitação da sua ONG."
    >
        <Button as-child
            ><RouterLink :to="{ name: 'organization.requests.create' }"
                >Criar solicitação</RouterLink
            ></Button
        >
    </EmptyState>
    <template v-else>
        <div class="grid gap-4 md:grid-cols-2" :aria-busy="loading">
            <RequestCard
                v-for="r in results"
                :key="r.id"
                :request="r"
                show-status
                :to="{ name: 'organization.request', params: { id: r.id } }"
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
