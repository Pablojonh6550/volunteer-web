<script setup lang="ts">
/**
 * Voluntários interessados em uma solicitação: a ONG aprova, recusa ou
 * conclui participações. Contatos aparecem somente após aprovação (LGPD).
 */
import { ArrowLeftIcon, MailIcon, PhoneIcon, UsersIcon } from '@lucide/vue';
import { computed, onMounted, ref } from 'vue';
import EmptyState from '@/components/common/EmptyState.vue';
import ErrorState from '@/components/common/ErrorState.vue';
import LoadingState from '@/components/common/LoadingState.vue';
import PageHeader from '@/components/common/PageHeader.vue';
import StatusBadge from '@/components/common/StatusBadge.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { useAsync } from '@/composables/useAsync';
import { getInitials } from '@/composables/useInitials';
import { useNotification } from '@/composables/useNotification';
import { requestService } from '@/services';
import type { RequestVolunteer } from '@/types';
import { formatDate } from '@/utils/format';

const props = defineProps<{ id: number }>();
const notify = useNotification();

const list = useAsync(() => requestService.volunteers(props.id));
const request = useAsync(() => requestService.get(props.id));
const busyId = ref<number | null>(null);

onMounted(() => {
    void list.execute();
    void request.execute();
});

const participations = computed(() => list.data.value ?? []);
const isFinal = computed(() =>
    ['completed', 'cancelled'].includes(request.data.value?.status ?? ''),
);

const ACTION_MESSAGES = {
    approved: 'Voluntário aprovado. O contato foi liberado.',
    rejected: 'Participação recusada.',
    completed: 'Participação concluída.',
} as const;

async function review(
    p: RequestVolunteer,
    status: keyof typeof ACTION_MESSAGES,
): Promise<void> {
    busyId.value = p.id;

    try {
        const updated = await requestService.reviewParticipation(p.id, status);
        list.data.value = participations.value.map((item) =>
            item.id === p.id ? { ...item, ...updated } : item,
        );
        notify.success(ACTION_MESSAGES[status]);
    } catch (e) {
        notify.error(e);
    } finally {
        busyId.value = null;
    }
}
</script>

<template>
    <Button variant="ghost" size="sm" as-child class="mb-4"
        ><RouterLink :to="{ name: 'organization.request', params: { id } }"
            ><ArrowLeftIcon aria-hidden="true" /> Voltar à
            solicitação</RouterLink
        ></Button
    >

    <PageHeader
        title="Voluntários interessados"
        :description="request.data.value?.title"
    />

    <ErrorState
        v-if="list.error.value"
        :message="list.error.value.message"
        @retry="list.execute()"
    />
    <LoadingState v-else-if="list.loading.value" />
    <EmptyState
        v-else-if="participations.length === 0"
        :icon="UsersIcon"
        title="Ainda não há interessados"
        description="Quando voluntários demonstrarem interesse, eles aparecerão aqui."
    />
    <ul v-else class="grid gap-4">
        <li v-for="p in participations" :key="p.id">
            <Card class="py-0">
                <CardContent
                    class="grid gap-4 p-5 md:grid-cols-[1fr_auto] md:items-start"
                >
                    <div class="flex gap-4">
                        <Avatar class="size-12">
                            <AvatarImage
                                v-if="p.volunteer?.avatar_url"
                                :src="p.volunteer.avatar_url"
                                alt=""
                            />
                            <AvatarFallback>{{
                                getInitials(p.volunteer?.name)
                            }}</AvatarFallback>
                        </Avatar>
                        <div class="grid gap-1">
                            <h2
                                class="flex flex-wrap items-center gap-2 font-medium"
                            >
                                {{ p.volunteer?.name }}
                                <StatusBadge
                                    kind="participation"
                                    :value="p.status"
                                />
                            </h2>
                            <p class="text-muted-foreground text-xs">
                                {{
                                    [p.volunteer?.city, p.volunteer?.state]
                                        .filter(Boolean)
                                        .join('/') || 'Cidade não informada'
                                }}
                                · interesse em {{ formatDate(p.created_at) }}
                            </p>
                            <p v-if="p.volunteer?.bio" class="text-sm">
                                {{ p.volunteer.bio }}
                            </p>
                            <blockquote
                                v-if="p.message"
                                class="border-primary-strong/40 text-muted-foreground mt-1 border-l-2 pl-3 text-sm italic"
                            >
                                “{{ p.message }}”
                            </blockquote>
                            <div
                                v-if="p.volunteer?.categories?.length"
                                class="mt-1 flex flex-wrap gap-1"
                            >
                                <span
                                    v-for="c in p.volunteer.categories"
                                    :key="c.id"
                                    class="bg-muted rounded px-1.5 py-0.5 text-xs"
                                    >{{ c.name }}</span
                                >
                            </div>
                            <div
                                v-if="p.contact"
                                class="bg-muted/50 mt-2 grid gap-1 rounded-md p-3 text-sm"
                            >
                                <p class="flex items-center gap-2">
                                    <MailIcon
                                        class="size-4"
                                        aria-hidden="true"
                                    /><a
                                        :href="`mailto:${p.contact.email}`"
                                        class="hover:underline"
                                        >{{ p.contact.email }}</a
                                    >
                                </p>
                                <p
                                    v-if="p.contact.phone"
                                    class="flex items-center gap-2"
                                >
                                    <PhoneIcon
                                        class="size-4"
                                        aria-hidden="true"
                                    />{{ p.contact.phone }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div
                        v-if="!isFinal"
                        class="flex flex-wrap gap-2 md:justify-end"
                    >
                        <template v-if="p.status === 'interested'">
                            <Button
                                size="sm"
                                :disabled="busyId === p.id"
                                :aria-label="`Aprovar ${p.volunteer?.name}`"
                                @click="review(p, 'approved')"
                                >Aprovar</Button
                            >
                            <Button
                                size="sm"
                                variant="outline"
                                :disabled="busyId === p.id"
                                :aria-label="`Recusar ${p.volunteer?.name}`"
                                @click="review(p, 'rejected')"
                                >Recusar</Button
                            >
                        </template>
                        <template v-else-if="p.status === 'approved'">
                            <Button
                                size="sm"
                                :disabled="busyId === p.id"
                                :aria-label="`Concluir participação de ${p.volunteer?.name}`"
                                @click="review(p, 'completed')"
                                >Concluir participação</Button
                            >
                            <Button
                                size="sm"
                                variant="outline"
                                :disabled="busyId === p.id"
                                @click="review(p, 'rejected')"
                                >Recusar</Button
                            >
                        </template>
                    </div>
                </CardContent>
            </Card>
        </li>
    </ul>
</template>
