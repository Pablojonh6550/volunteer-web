<script setup lang="ts">
/**
 * Mapa de ONGs próximas. A lista ao lado do mapa contém as mesmas
 * informações (alternativa acessível ao mapa).
 */
import { CrosshairIcon, MapPinIcon } from '@lucide/vue';
import { computed, onMounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import EmptyState from '@/components/common/EmptyState.vue';
import ErrorState from '@/components/common/ErrorState.vue';
import LoadingState from '@/components/common/LoadingState.vue';
import PageHeader from '@/components/common/PageHeader.vue';
import MapView from '@/components/map/MapView.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { NativeSelect } from '@/components/ui/native-select';
import { useAsync } from '@/composables/useAsync';
import { useLocation } from '@/composables/useLocation';
import type { MapMarker } from '@/composables/useMap';
import { organizationService } from '@/services';
import { useAuthStore } from '@/stores/auth';
import type { Coordinates } from '@/types';
import { formatDistance } from '@/utils/format';
import { hasCoordinates, RADIUS_OPTIONS } from '@/utils/geo';

const auth = useAuthStore();
const router = useRouter();
const location = useLocation();

const radius = ref('10');
const origin = ref<Coordinates | null>(
    location.coords.value ??
        (hasCoordinates(auth.volunteer)
            ? {
                  latitude: auth.volunteer.latitude,
                  longitude: auth.volunteer.longitude,
              }
            : null),
);

const { data, loading, error, execute } = useAsync(() =>
    organizationService.list(
        origin.value
            ? {
                  lat: origin.value.latitude,
                  lng: origin.value.longitude,
                  radius: Number(radius.value) || undefined,
              }
            : {},
    ),
);

const organizations = computed(() => data.value?.data ?? []);
const markers = computed<MapMarker[]>(() =>
    organizations.value.filter(hasCoordinates).map((o) => ({
        id: o.id,
        latitude: o.latitude,
        longitude: o.longitude,
        title: o.name,
        subtitle: [
            o.distance_km != null ? formatDistance(o.distance_km) : null,
            `${o.open_requests_count ?? 0} solicitação(ões) aberta(s)`,
        ]
            .filter(Boolean)
            .join(' · '),
        actionLabel: 'Ver ONG',
        variant: 'organization',
    })),
);

async function useMyLocation(): Promise<void> {
    const coords = await location.locate();

    if (coords) {
        origin.value = coords;
    }
}

const openOrganization = (id: number) =>
    router.push({ name: 'volunteer.organization', params: { id } });

onMounted(execute);
watch([origin, radius], () => execute());
</script>

<template>
    <PageHeader
        title="Mapa de ONGs"
        description="Encontre organizações próximas de você."
    >
        <template #actions>
            <Button
                variant="outline"
                :disabled="location.loading.value"
                @click="useMyLocation"
            >
                <CrosshairIcon aria-hidden="true" />
                {{
                    location.loading.value
                        ? 'Localizando…'
                        : 'Usar minha localização'
                }}
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
    <p v-if="!origin" class="text-muted-foreground mb-4 text-sm">
        Sem localização definida: exibindo todas as ONGs. Use sua localização ou
        cadastre-a no perfil para filtrar por distância.
    </p>

    <div v-if="origin" class="mb-4 flex max-w-xs items-end gap-2">
        <div class="grid flex-1 gap-2">
            <Label for="radius">Raio de busca</Label>
            <NativeSelect
                id="radius"
                v-model="radius"
                :options="
                    RADIUS_OPTIONS.map((km) => ({
                        value: String(km),
                        label: `${km} km`,
                    }))
                "
                placeholder="Sem limite"
            />
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1fr_22rem]">
        <MapView
            label="Mapa de ONGs"
            :markers="markers"
            :user-location="origin"
            :radius-km="origin && radius ? Number(radius) : null"
            height="520px"
            @select="openOrganization"
        />

        <section aria-labelledby="list-title" class="grid content-start gap-3">
            <h2 id="list-title" class="font-semibold">
                {{ organizations.length }} ONG(s) encontrada(s)
            </h2>
            <ErrorState
                v-if="error"
                :message="error.message"
                @retry="execute()"
            />
            <LoadingState v-else-if="loading" :rows="3" />
            <EmptyState
                v-else-if="organizations.length === 0"
                :icon="MapPinIcon"
                title="Nenhuma ONG nesse raio"
                description="Tente aumentar a distância."
            />
            <ul v-else class="grid max-h-[520px] gap-2 overflow-y-auto pr-1">
                <li v-for="o in organizations" :key="o.id">
                    <RouterLink
                        :to="{
                            name: 'volunteer.organization',
                            params: { id: o.id },
                        }"
                        class="hover:bg-accent focus-visible:ring-ring/50 block rounded-lg border p-3 focus-visible:ring-[3px] focus-visible:outline-none"
                    >
                        <p class="font-medium">{{ o.name }}</p>
                        <p class="text-muted-foreground text-xs">
                            {{
                                [o.neighborhood, o.city]
                                    .filter(Boolean)
                                    .join(', ') || 'Endereço não informado'
                            }}
                            <template v-if="o.distance_km != null">
                                · {{ formatDistance(o.distance_km) }}</template
                            >
                        </p>
                        <p class="text-muted-foreground text-xs">
                            {{ o.open_requests_count ?? 0 }} solicitação(ões)
                            aberta(s)
                        </p>
                    </RouterLink>
                </li>
            </ul>
        </section>
    </div>
</template>
