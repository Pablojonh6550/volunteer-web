<script setup lang="ts">
/**
 * Componente de mapa (Leaflet). Encapsula `useMap` e reage às props.
 *
 * Acessibilidade: o mapa é um complemento visual — as mesmas informações
 * estão sempre disponíveis em lista na página que o utiliza.
 */
import { ref, watch } from 'vue';
import { useMap } from '@/composables/useMap';
import type { MapMarker } from '@/composables/useMap';
import type { Coordinates } from '@/types';

const props = withDefaults(
    defineProps<{
        markers?: MapMarker[];
        center?: Coordinates | null;
        userLocation?: Coordinates | null;
        radiusKm?: number | null;
        zoom?: number;
        label: string;
        height?: string;
        fit?: boolean;
    }>(),
    { markers: () => [], zoom: 13, height: '420px', fit: true },
);

const emit = defineEmits<{
    select: [id: number];
    pick: [coords: Coordinates];
}>();

const el = ref<HTMLElement | null>(null);
const map = useMap(el, {
    center: props.center ?? props.userLocation ?? undefined,
    zoom: props.zoom,
    onSelect: (id) => emit('select', id),
    onClick: (coords) => emit('pick', coords),
});

function render(): void {
    if (!map.ready.value) {
        return;
    }

    map.setMarkers(props.markers);
    map.setUserLocation(
        props.userLocation ?? null,
        props.radiusKm ?? undefined,
    );

    if (props.fit && (props.markers.length > 0 || props.userLocation)) {
        map.fitToContent();
    } else if (props.center) {
        map.setView(props.center);
    }
}

watch(
    [
        map.ready,
        () => props.markers,
        () => props.userLocation,
        () => props.radiusKm,
        () => props.center,
    ],
    render,
    { deep: true },
);

defineExpose({ setView: map.setView });
</script>

<template>
    <div
        ref="el"
        role="region"
        :aria-label="label"
        class="bg-muted relative z-0 w-full overflow-hidden rounded-xl border"
        :style="{ height }"
    />
</template>
