<script setup lang="ts">
import {
    CheckCircleIcon,
    ClipboardListIcon,
    HourglassIcon,
    PlusIcon,
    UsersIcon,
} from '@lucide/vue';
import { computed, onMounted } from 'vue';
import ErrorState from '@/components/common/ErrorState.vue';
import LoadingState from '@/components/common/LoadingState.vue';
import PageHeader from '@/components/common/PageHeader.vue';
import StatCard from '@/components/common/StatCard.vue';
import RequestCard from '@/components/requests/RequestCard.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { useAsync } from '@/composables/useAsync';
import { catalogService, requestService } from '@/services';
import { useAuthStore } from '@/stores/auth';
import type { OrganizationDashboard } from '@/types';

const auth = useAuthStore();
const stats = useAsync(() => catalogService.dashboard<OrganizationDashboard>());
const recent = useAsync(() =>
    requestService.search({ mine: true, per_page: 4 }),
);

onMounted(() => {
    void stats.execute();
    void recent.execute();
});

const s = computed(() => stats.data.value);
</script>

<template>
    <PageHeader
        :title="auth.organization?.name ?? 'Painel'"
        description="Visão geral das suas solicitações e voluntários."
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

    <Alert v-if="s && !s.profile_complete" class="mb-6">
        <AlertTitle>Complete o perfil da ONG</AlertTitle>
        <AlertDescription>
            Adicione descrição e localização para que voluntários encontrem sua
            organização no mapa.
            <RouterLink
                :to="{ name: 'organization.profile' }"
                class="text-primary font-medium underline"
                >Completar perfil</RouterLink
            >
        </AlertDescription>
    </Alert>

    <ErrorState
        v-if="stats.error.value"
        :message="stats.error.value.message"
        @retry="stats.execute()"
    />
    <LoadingState v-else-if="stats.loading.value && !s" :rows="1" />
    <section
        v-else-if="s"
        aria-label="Resumo"
        class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4"
    >
        <StatCard
            label="Solicitações abertas"
            :value="s.requests_by_status.open"
            :icon="ClipboardListIcon"
        />
        <StatCard
            label="Em andamento"
            :value="s.requests_by_status.in_progress"
            :icon="HourglassIcon"
        />
        <StatCard
            label="Interessados aguardando"
            :value="s.pending_interests"
            :icon="UsersIcon"
        />
        <StatCard
            label="Voluntários aprovados"
            :value="s.approved_volunteers"
            :icon="CheckCircleIcon"
        />
    </section>

    <section aria-labelledby="recent-title">
        <div class="mb-4 flex items-center justify-between">
            <h2 id="recent-title" class="text-lg font-semibold">
                Solicitações recentes
            </h2>
            <Button variant="link" as-child
                ><RouterLink :to="{ name: 'organization.requests' }"
                    >Ver todas</RouterLink
                ></Button
            >
        </div>
        <LoadingState v-if="recent.loading.value" :rows="2" />
        <div v-else class="grid gap-4 md:grid-cols-2">
            <RequestCard
                v-for="r in recent.data.value?.data ?? []"
                :key="r.id"
                :request="r"
                show-status
                :to="{ name: 'organization.request', params: { id: r.id } }"
            />
        </div>
    </section>
</template>
