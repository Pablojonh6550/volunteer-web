<script setup lang="ts">
/** "Minhas atividades": participações e histórico do voluntário. */
import { ActivityIcon } from '@lucide/vue';
import { onMounted, ref, watch } from 'vue';
import EmptyState from '@/components/common/EmptyState.vue';
import ErrorState from '@/components/common/ErrorState.vue';
import LoadingState from '@/components/common/LoadingState.vue';
import PageHeader from '@/components/common/PageHeader.vue';
import Pagination from '@/components/common/Pagination.vue';
import StatusBadge from '@/components/common/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { useVolunteer } from '@/composables/useVolunteer';
import { REQUEST_VOLUNTEER_STATUS_LABELS } from '@/types';
import type { RequestVolunteerStatus } from '@/types';
import { formatDate } from '@/utils/format';
import { cn } from '@/lib/utils';

const { activities } = useVolunteer();
const status = ref<RequestVolunteerStatus | ''>('');
const page = ref(1);

const tabs: { value: RequestVolunteerStatus | ''; label: string }[] = [
    { value: '', label: 'Todas' },
    ...(
        Object.keys(REQUEST_VOLUNTEER_STATUS_LABELS) as RequestVolunteerStatus[]
    ).map((value) => ({
        value,
        label: REQUEST_VOLUNTEER_STATUS_LABELS[value],
    })),
];

const load = () =>
    activities.execute({ status: status.value || undefined, page: page.value });

onMounted(load);
watch(status, () => {
    page.value = 1;
    void load();
});
</script>

<template>
    <PageHeader
        title="Minhas atividades"
        description="Acompanhe suas participações e seu histórico de voluntariado."
    />

    <div
        role="group"
        aria-label="Filtrar por situação"
        class="mb-6 flex flex-wrap gap-2"
    >
        <Button
            v-for="tab in tabs"
            :key="tab.value"
            size="sm"
            :variant="status === tab.value ? 'default' : 'outline'"
            :aria-pressed="status === tab.value"
            @click="status = tab.value"
        >
            {{ tab.label }}
        </Button>
    </div>

    <ErrorState
        v-if="activities.error.value"
        :message="activities.error.value.message"
        @retry="load"
    />
    <LoadingState v-else-if="activities.loading.value" />
    <EmptyState
        v-else-if="!activities.data.value?.data.length"
        :icon="ActivityIcon"
        title="Nenhuma atividade por aqui"
        description="Pesquise solicitações e demonstre interesse para começar."
    >
        <Button as-child
            ><RouterLink :to="{ name: 'volunteer.requests' }"
                >Pesquisar solicitações</RouterLink
            ></Button
        >
    </EmptyState>
    <template v-else>
        <ul class="grid gap-3">
            <li
                v-for="p in activities.data.value.data"
                :key="p.id"
                :class="
                    cn(
                        'flex flex-col gap-2 rounded-xl border p-4 sm:flex-row sm:items-center sm:justify-between',
                    )
                "
            >
                <div>
                    <RouterLink
                        v-if="p.request"
                        :to="{
                            name: 'volunteer.request',
                            params: { id: p.request.id },
                        }"
                        class="font-medium hover:underline"
                        >{{ p.request.title }}</RouterLink
                    >
                    <p class="text-muted-foreground text-sm">
                        {{ p.request?.organization?.name }} · atualizado em
                        {{ formatDate(p.updated_at) }}
                    </p>
                </div>
                <StatusBadge kind="participation" :value="p.status" />
            </li>
        </ul>
        <Pagination
            v-if="activities.data.value.meta"
            :current-page="activities.data.value.meta.current_page"
            :last-page="activities.data.value.meta.last_page"
            :total="activities.data.value.meta.total"
            @change="
                (n) => {
                    page = n;
                    load();
                }
            "
        />
    </template>
</template>
