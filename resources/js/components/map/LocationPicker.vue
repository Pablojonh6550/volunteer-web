<script setup lang="ts">
/**
 * Seleção de localização: botão "usar minha localização" (geolocalização
 * do navegador, sob demanda) ou clique no mapa. Emite latitude/longitude.
 */
import { CrosshairIcon, XIcon } from '@lucide/vue';
import { computed } from 'vue';
import MapView from '@/components/map/MapView.vue';
import { Button } from '@/components/ui/button';
import { useLocation } from '@/composables/useLocation';
import type { Coordinates } from '@/types';

const latitude = defineModel<number | null>('latitude', { default: null });
const longitude = defineModel<number | null>('longitude', { default: null });

defineProps<{ disabled?: boolean }>();

const location = useLocation();

const value = computed<Coordinates | null>(() =>
    latitude.value !== null && longitude.value !== null
        ? { latitude: latitude.value, longitude: longitude.value }
        : null,
);

const markers = computed(() =>
    value.value
        ? [
              {
                  id: 0,
                  ...value.value,
                  title: 'Local selecionado',
                  variant: 'request' as const,
              },
          ]
        : [],
);

function set(coords: Coordinates | null): void {
    latitude.value = coords?.latitude ?? null;
    longitude.value = coords?.longitude ?? null;
}

async function useMyLocation(): Promise<void> {
    const coords = await location.locate();

    if (coords) {
        set(coords);
    }
}
</script>

<template>
    <div class="grid gap-3">
        <div class="flex flex-wrap items-center gap-2">
            <Button
                type="button"
                variant="outline"
                size="sm"
                :disabled="disabled || location.loading.value"
                @click="useMyLocation"
            >
                <CrosshairIcon aria-hidden="true" />
                {{
                    location.loading.value
                        ? 'Localizando…'
                        : 'Usar minha localização'
                }}
            </Button>
            <Button
                v-if="value"
                type="button"
                variant="ghost"
                size="sm"
                :disabled="disabled"
                @click="set(null)"
            >
                <XIcon aria-hidden="true" /> Remover localização
            </Button>
            <p class="text-muted-foreground text-xs" aria-live="polite">
                <template v-if="value"
                    >Selecionado: {{ value.latitude.toFixed(5) }},
                    {{ value.longitude.toFixed(5) }}</template
                >
                <template v-else
                    >Nenhum ponto selecionado — clique no mapa ou use sua
                    localização.</template
                >
            </p>
        </div>
        <p
            v-if="location.error.value"
            class="text-destructive text-sm"
            role="alert"
        >
            {{ location.error.value }}
        </p>
        <MapView
            label="Mapa para selecionar a localização. Clique para posicionar o marcador."
            :markers="markers"
            :center="value"
            :fit="false"
            :zoom="value ? 15 : 12"
            height="300px"
            @pick="(c) => !disabled && set(c)"
        />
    </div>
</template>
