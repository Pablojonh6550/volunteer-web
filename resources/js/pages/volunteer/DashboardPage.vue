<script setup lang="ts">
import {
    CheckCircleIcon,
    ClipboardListIcon,
    HourglassIcon,
    MapPinIcon,
    SearchIcon,
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
import type { VolunteerDashboard } from '@/types';
import { hasCoordinates } from '@/utils/geo';

const auth = useAuthStore();
const stats = useAsync(() => catalogService.dashboard<VolunteerDashboard>());

// Sugestões: solicitações próximas (se houver localização) ou as mais recentes.
const suggestions = useAsync(() => {
    const v = auth.volunteer;

    return requestService.search(
        hasCoordinates(v)
            ? {
                  lat: v.latitude,
                  lng: v.longitude,
                  radius: 25,
                  sort: 'distance',
                  per_page: 4,
              }
            : { per_page: 4 },
    );
});

onMounted(() => {
    void stats.execute();
    void suggestions.execute();
});

const s = computed(() => stats.data.value);
</script>

<template>
    <PageHeader
        :title="`Olá, ${auth.user?.name?.split(' ')[0] ?? ''}!`"
        description="Acompanhe suas participações e encontre novas oportunidades."
    >
        <template #actions>
            <Button as-child
                ><RouterLink :to="{ name: 'volunteer.requests' }"
                    ><SearchIcon aria-hidden="true" /> Pesquisar
                    solicitações</RouterLink
                ></Button
            >
        </template>
    </PageHeader>

    <Alert v-if="s && !s.profile_complete" class="mb-6">
        <AlertTitle>Complete seu perfil</AlertTitle>
        <AlertDescription>
            Informe suas áreas de interesse e, se quiser, autorize o uso da
            localização para ver oportunidades próximas.
            <RouterLink
                :to="{ name: 'volunteer.profile' }"
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
            label="Aguardando resposta"
            :value="s.participations_by_status.interested"
            :icon="HourglassIcon"
        />
        <StatCard
            label="Aprovadas"
            :value="s.participations_by_status.approved"
            :icon="CheckCircleIcon"
        />
        <StatCard
            label="Concluídas"
            :value="s.participations_by_status.completed"
            :icon="ClipboardListIcon"
        />
        <StatCard
            label="Oportunidades a até 10 km"
            :value="s.nearby_requests ?? '—'"
            :icon="MapPinIcon"
            :hint="
                s.nearby_requests === null
                    ? 'Ative a localização no perfil'
                    : undefined
            "
        />
    </section>

    <section aria-labelledby="suggestions-title">
        <h2 id="suggestions-title" class="mb-4 text-lg font-semibold">
            {{
                hasCoordinates(auth.volunteer)
                    ? 'Oportunidades perto de você'
                    : 'Oportunidades recentes'
            }}
        </h2>
        <LoadingState v-if="suggestions.loading.value" :rows="2" />
        <div v-else class="grid gap-4 md:grid-cols-2">
            <RequestCard
                v-for="r in suggestions.data.value?.data ?? []"
                :key="r.id"
                :request="r"
                :to="{ name: 'volunteer.request', params: { id: r.id } }"
            />
        </div>
    </section>
</template>
