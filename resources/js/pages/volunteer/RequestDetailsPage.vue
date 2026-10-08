<script setup lang="ts">
/**
 * Detalhes da solicitação (visão do voluntário) e manifestação de interesse.
 */
import {
    ArrowLeftIcon,
    BuildingIcon,
    CalendarIcon,
    HashIcon,
    MapPinIcon,
} from '@lucide/vue';
import { computed, onMounted, ref } from 'vue';
import ConfirmDialog from '@/components/common/ConfirmDialog.vue';
import ErrorState from '@/components/common/ErrorState.vue';
import LoadingState from '@/components/common/LoadingState.vue';
import StatusBadge from '@/components/common/StatusBadge.vue';
import MapView from '@/components/map/MapView.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useRequest } from '@/composables/useRequest';
import { formatPeriod } from '@/utils/format';
import { hasCoordinates } from '@/utils/geo';

const props = defineProps<{ id: number }>();
const {
    request,
    loading,
    error,
    acting,
    load,
    expressInterest,
    cancelInterest,
} = useRequest();

const message = ref('');
const confirmCancel = ref(false);

onMounted(() => load(props.id));

const participation = computed(() => request.value?.my_participation ?? null);
const canApply = computed(
    () =>
        request.value &&
        ['open', 'in_progress'].includes(request.value.status) &&
        (!participation.value || participation.value.status === 'cancelled'),
);
const canCancel = computed(
    () =>
        participation.value &&
        ['interested', 'approved'].includes(participation.value.status),
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

async function apply(): Promise<void> {
    if (await expressInterest(message.value || undefined)) {
        message.value = '';
    }
}

async function cancel(): Promise<void> {
    await cancelInterest();
    confirmCancel.value = false;
}
</script>

<template>
    <Button variant="ghost" size="sm" as-child class="mb-4"
        ><RouterLink :to="{ name: 'volunteer.requests' }"
            ><ArrowLeftIcon aria-hidden="true" /> Voltar à pesquisa</RouterLink
        ></Button
    >

    <ErrorState v-if="error" :message="error.message" @retry="load(id)" />
    <LoadingState v-else-if="loading || !request" />
    <article v-else class="grid gap-6 lg:grid-cols-[1fr_22rem]">
        <div class="grid content-start gap-6">
            <header class="space-y-3">
                <div class="flex flex-wrap gap-2">
                    <StatusBadge kind="type" :value="request.type" />
                    <StatusBadge kind="priority" :value="request.priority" />
                    <StatusBadge kind="status" :value="request.status" />
                </div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    {{ request.title }}
                </h1>
                <RouterLink
                    v-if="request.organization"
                    :to="{
                        name: 'volunteer.organization',
                        params: { id: request.organization.id },
                    }"
                    class="text-muted-foreground flex items-center gap-2 text-sm hover:underline"
                >
                    <BuildingIcon class="size-4" aria-hidden="true" />
                    {{ request.organization.name }}
                </RouterLink>
            </header>

            <p class="leading-relaxed whitespace-pre-line">
                {{ request.description }}
            </p>

            <dl class="grid gap-3 text-sm sm:grid-cols-2">
                <div class="flex items-center gap-2">
                    <CalendarIcon class="size-4" aria-hidden="true" />
                    <dt class="sr-only">Período</dt>
                    <dd>
                        {{ formatPeriod(request.start_date, request.end_date) }}
                    </dd>
                </div>
                <div v-if="request.quantity" class="flex items-center gap-2">
                    <HashIcon class="size-4" aria-hidden="true" />
                    <dt>Quantidade/vagas:</dt>
                    <dd>{{ request.quantity }}</dd>
                </div>
                <div v-if="request.category" class="flex items-center gap-2">
                    <dt>Categoria:</dt>
                    <dd>{{ request.category.name }}</dd>
                </div>
                <div
                    v-if="request.organization?.city"
                    class="flex items-center gap-2"
                >
                    <MapPinIcon class="size-4" aria-hidden="true" />
                    <dt class="sr-only">Cidade</dt>
                    <dd>
                        {{ request.organization.city }}/{{
                            request.organization.state
                        }}
                    </dd>
                </div>
            </dl>

            <MapView
                v-if="markers.length"
                label="Local da atividade"
                :markers="markers"
                :zoom="15"
                height="280px"
            />
        </div>

        <aside>
            <Card class="sticky top-4 py-0">
                <CardContent class="grid gap-4 p-5">
                    <h2 class="font-semibold">Sua participação</h2>

                    <div
                        v-if="
                            participation &&
                            participation.status !== 'cancelled'
                        "
                        class="grid gap-2 text-sm"
                    >
                        <p class="flex items-center gap-2">
                            Situação:
                            <StatusBadge
                                kind="participation"
                                :value="participation.status"
                            />
                        </p>
                        <p
                            v-if="participation.status === 'interested'"
                            class="text-muted-foreground"
                        >
                            A ONG vai analisar seu interesse. Você será avisado
                            por e-mail.
                        </p>
                        <p
                            v-else-if="participation.status === 'approved'"
                            class="text-muted-foreground"
                        >
                            Você foi aprovado! A ONG recebeu seu contato e
                            falará com você.
                        </p>
                    </div>

                    <form
                        v-if="canApply"
                        class="grid gap-3"
                        @submit.prevent="apply"
                    >
                        <div class="grid gap-2">
                            <Label for="interest-message"
                                >Mensagem para a ONG (opcional)</Label
                            >
                            <Textarea
                                id="interest-message"
                                v-model="message"
                                rows="3"
                                maxlength="1000"
                                placeholder="Conte como você pode ajudar e sua disponibilidade."
                            />
                        </div>
                        <Button type="submit" :disabled="acting">{{
                            acting ? 'Enviando…' : 'Quero ajudar'
                        }}</Button>
                    </form>

                    <p
                        v-else-if="
                            !participation &&
                            !['open', 'in_progress'].includes(request.status)
                        "
                        class="text-muted-foreground text-sm"
                    >
                        Esta solicitação não está mais aceitando interessados.
                    </p>

                    <Button
                        v-if="canCancel"
                        variant="outline"
                        :disabled="acting"
                        @click="confirmCancel = true"
                        >Cancelar interesse</Button
                    >
                </CardContent>
            </Card>
        </aside>
    </article>

    <ConfirmDialog
        v-model:open="confirmCancel"
        title="Cancelar interesse?"
        description="A ONG deixará de contar com você nesta solicitação. Você poderá demonstrar interesse novamente depois."
        confirm-label="Cancelar interesse"
        destructive
        :loading="acting"
        @confirm="cancel"
    />
</template>
