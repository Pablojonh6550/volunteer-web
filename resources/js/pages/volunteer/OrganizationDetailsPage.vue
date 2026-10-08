<script setup lang="ts">
import { GlobeIcon, MailIcon, MapPinIcon, PhoneIcon } from '@lucide/vue';
import { computed, onMounted } from 'vue';
import EmptyState from '@/components/common/EmptyState.vue';
import ErrorState from '@/components/common/ErrorState.vue';
import LoadingState from '@/components/common/LoadingState.vue';
import PageHeader from '@/components/common/PageHeader.vue';
import MapView from '@/components/map/MapView.vue';
import RequestCard from '@/components/requests/RequestCard.vue';
import { Card, CardContent } from '@/components/ui/card';
import { useOrganization } from '@/composables/useOrganization';
import { hasCoordinates } from '@/utils/geo';

const props = defineProps<{ id: number }>();
const { organization, loading, error, load } = useOrganization();

onMounted(() => load(props.id));

const address = computed(() => {
    const o = organization.value;

    return o
        ? [
              o.address && `${o.address}${o.number ? `, ${o.number}` : ''}`,
              o.neighborhood,
              o.city && `${o.city}/${o.state ?? ''}`,
          ]
              .filter(Boolean)
              .join(' · ')
        : '';
});

const markers = computed(() =>
    hasCoordinates(organization.value)
        ? [
              {
                  id: organization.value.id,
                  latitude: organization.value.latitude,
                  longitude: organization.value.longitude,
                  title: organization.value.name,
              },
          ]
        : [],
);
</script>

<template>
    <ErrorState v-if="error" :message="error.message" @retry="load(id)" />
    <LoadingState v-else-if="loading || !organization" />
    <template v-else>
        <PageHeader
            :title="organization.name"
            :description="address || undefined"
        />
        <div class="grid gap-6 lg:grid-cols-[1fr_20rem]">
            <div class="grid content-start gap-6">
                <p
                    v-if="organization.description"
                    class="leading-relaxed whitespace-pre-line"
                >
                    {{ organization.description }}
                </p>
                <section aria-labelledby="org-requests">
                    <h2 id="org-requests" class="mb-3 text-lg font-semibold">
                        Solicitações abertas
                    </h2>
                    <EmptyState
                        v-if="!organization.requests?.length"
                        title="Nenhuma solicitação aberta no momento."
                    />
                    <div v-else class="grid gap-4 md:grid-cols-2">
                        <RequestCard
                            v-for="r in organization.requests"
                            :key="r.id"
                            :request="{ ...r, organization }"
                            :to="{
                                name: 'volunteer.request',
                                params: { id: r.id },
                            }"
                        />
                    </div>
                </section>
            </div>
            <aside class="grid content-start gap-4">
                <Card class="py-0">
                    <CardContent class="grid gap-3 p-5 text-sm">
                        <h2 class="font-semibold">Contato</h2>
                        <p
                            v-if="organization.email"
                            class="flex items-center gap-2"
                        >
                            <MailIcon class="size-4" aria-hidden="true" /><a
                                :href="`mailto:${organization.email}`"
                                class="underline-offset-4 hover:underline"
                                >{{ organization.email }}</a
                            >
                        </p>
                        <p
                            v-if="organization.phone"
                            class="flex items-center gap-2"
                        >
                            <PhoneIcon class="size-4" aria-hidden="true" />{{
                                organization.phone
                            }}
                        </p>
                        <p
                            v-if="organization.website"
                            class="flex items-center gap-2"
                        >
                            <GlobeIcon class="size-4" aria-hidden="true" /><a
                                :href="organization.website"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="underline-offset-4 hover:underline"
                                >Site</a
                            >
                        </p>
                        <p v-if="address" class="flex items-start gap-2">
                            <MapPinIcon
                                class="mt-0.5 size-4"
                                aria-hidden="true"
                            />{{ address }}
                        </p>
                    </CardContent>
                </Card>
                <MapView
                    v-if="markers.length"
                    :label="`Localização de ${organization.name}`"
                    :markers="markers"
                    :zoom="15"
                    height="240px"
                />
            </aside>
        </div>
    </template>
</template>
