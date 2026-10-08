<script setup lang="ts">
/**
 * Detalhes da solicitação (visão da ONG): editar, mudar status,
 * concluir, cancelar/excluir e acessar interessados.
 */
import {
    ArrowLeftIcon,
    CheckIcon,
    PencilIcon,
    PlayIcon,
    Trash2Icon,
    UsersIcon,
} from '@lucide/vue';
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import ConfirmDialog from '@/components/common/ConfirmDialog.vue';
import ErrorState from '@/components/common/ErrorState.vue';
import LoadingState from '@/components/common/LoadingState.vue';
import StatusBadge from '@/components/common/StatusBadge.vue';
import MapView from '@/components/map/MapView.vue';
import { Button } from '@/components/ui/button';
import { useRequest } from '@/composables/useRequest';
import { formatDate, formatPeriod } from '@/utils/format';
import { hasCoordinates } from '@/utils/geo';

const props = defineProps<{ id: number }>();
const router = useRouter();
const { request, loading, error, acting, load, updateStatus, remove } =
    useRequest();

const confirmComplete = ref(false);
const confirmDelete = ref(false);

onMounted(() => load(props.id));

const isFinal = computed(
    () =>
        request.value &&
        ['completed', 'cancelled'].includes(request.value.status),
);
const markers = computed(() =>
    hasCoordinates(request.value)
        ? [
              {
                  id: request.value.id,
                  latitude: request.value.latitude,
                  longitude: request.value.longitude,
                  title: request.value.title,
                  variant: 'request' as const,
              },
          ]
        : [],
);

async function complete(): Promise<void> {
    await updateStatus(
        'completed',
        'Solicitação concluída. Participações aprovadas foram registradas.',
    );
    confirmComplete.value = false;
}

async function destroy(): Promise<void> {
    const deleted = await remove();
    confirmDelete.value = false;

    if (deleted) {
        await router.push({ name: 'organization.requests' });
    }
}
</script>

<template>
    <Button variant="ghost" size="sm" as-child class="mb-4"
        ><RouterLink :to="{ name: 'organization.requests' }"
            ><ArrowLeftIcon aria-hidden="true" /> Minhas
            solicitações</RouterLink
        ></Button
    >

    <ErrorState v-if="error" :message="error.message" @retry="load(id)" />
    <LoadingState v-else-if="loading || !request" />
    <article v-else class="grid gap-6">
        <header
            class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between"
        >
            <div class="space-y-3">
                <div class="flex flex-wrap gap-2">
                    <StatusBadge kind="status" :value="request.status" />
                    <StatusBadge kind="type" :value="request.type" />
                    <StatusBadge kind="priority" :value="request.priority" />
                </div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    {{ request.title }}
                </h1>
                <p class="text-muted-foreground text-sm">
                    Publicada em {{ formatDate(request.created_at) }} ·
                    {{ formatPeriod(request.start_date, request.end_date) }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <Button as-child variant="default">
                    <RouterLink
                        :to="{
                            name: 'organization.request.volunteers',
                            params: { id: request.id },
                        }"
                    >
                        <UsersIcon aria-hidden="true" /> Interessados ({{
                            request.interested_count ?? 0
                        }})
                    </RouterLink>
                </Button>
                <template v-if="!isFinal">
                    <Button as-child variant="outline"
                        ><RouterLink
                            :to="{
                                name: 'organization.requests.edit',
                                params: { id: request.id },
                            }"
                            ><PencilIcon aria-hidden="true" />
                            Editar</RouterLink
                        ></Button
                    >
                    <Button
                        v-if="request.status === 'open'"
                        variant="outline"
                        :disabled="acting"
                        @click="
                            updateStatus(
                                'in_progress',
                                'Solicitação em andamento.',
                            )
                        "
                        ><PlayIcon aria-hidden="true" /> Iniciar</Button
                    >
                    <Button
                        variant="outline"
                        :disabled="acting"
                        @click="confirmComplete = true"
                        ><CheckIcon aria-hidden="true" /> Concluir</Button
                    >
                    <Button
                        variant="destructive"
                        :disabled="acting"
                        @click="confirmDelete = true"
                        ><Trash2Icon aria-hidden="true" />
                        Cancelar/excluir</Button
                    >
                </template>
            </div>
        </header>

        <div class="grid gap-6 lg:grid-cols-[1fr_22rem]">
            <div class="grid content-start gap-4">
                <p class="leading-relaxed whitespace-pre-line">
                    {{ request.description }}
                </p>
                <dl class="grid gap-2 text-sm sm:grid-cols-2">
                    <div v-if="request.category">
                        <dt class="text-muted-foreground">Categoria</dt>
                        <dd>{{ request.category.name }}</dd>
                    </div>
                    <div v-if="request.quantity">
                        <dt class="text-muted-foreground">Quantidade/vagas</dt>
                        <dd>{{ request.quantity }}</dd>
                    </div>
                </dl>
            </div>
            <MapView
                v-if="markers.length"
                label="Local da atividade"
                :markers="markers"
                :zoom="15"
                height="260px"
            />
        </div>
    </article>

    <ConfirmDialog
        v-model:open="confirmComplete"
        title="Concluir solicitação?"
        description="Voluntários aprovados terão a participação registrada como concluída e interessados pendentes serão recusados. Esta ação não pode ser desfeita."
        confirm-label="Concluir"
        :loading="acting"
        @confirm="complete"
    />
    <ConfirmDialog
        v-model:open="confirmDelete"
        title="Cancelar ou excluir?"
        description="Se houver voluntários vinculados, a solicitação será cancelada (preservando o histórico). Caso contrário, será excluída definitivamente."
        confirm-label="Confirmar"
        destructive
        :loading="acting"
        @confirm="destroy"
    />
</template>
