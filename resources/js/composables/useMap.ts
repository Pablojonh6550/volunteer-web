/**
 * Mapa interativo com Leaflet + OpenStreetMap.
 *
 * - Leaflet é carregado sob demanda (import dinâmico), então só entra no
 *   bundle das páginas que exibem mapa (performance).
 * - Popups são montados com `textContent` — nunca innerHTML com dados da API —
 *   evitando XSS.
 * - Marcadores são focáveis por teclado (`keyboard: true`) e têm `title`.
 *
 * Uso:
 *   const mapEl = ref<HTMLElement>();
 *   const map = useMap(mapEl, { center, zoom: 13 });
 *   map.setMarkers([{ id: 1, latitude, longitude, title: 'ONG X' }]);
 */
import type * as Leaflet from 'leaflet';
import { onBeforeUnmount, onMounted, ref, shallowRef } from 'vue';
import type { Ref } from 'vue';
import type { Coordinates } from '@/types';
import { DEFAULT_CENTER } from '@/utils/geo';

export interface MapMarker extends Coordinates {
    id: number;
    title: string;
    subtitle?: string;
    /** Texto do link exibido no popup. */
    actionLabel?: string;
    variant?: 'organization' | 'request' | 'user';
}

export interface UseMapOptions {
    center?: Coordinates;
    zoom?: number;
    /** Chamado ao clicar no link do popup de um marcador. */
    onSelect?: (id: number) => void;
    /** Chamado ao clicar em um ponto vazio do mapa. */
    onClick?: (coords: Coordinates) => void;
}

type LeafletModule = typeof Leaflet;

export function useMap(
    el: Ref<HTMLElement | null | undefined>,
    options: UseMapOptions = {},
) {
    const ready = ref(false);
    const map = shallowRef<Leaflet.Map | null>(null);
    let L: LeafletModule | null = null;
    let markersLayer: Leaflet.LayerGroup | null = null;
    let userLayer: Leaflet.LayerGroup | null = null;
    let pendingMarkers: MapMarker[] | null = null;

    function icon(
        variant: MapMarker['variant'] = 'organization',
    ): Leaflet.DivIcon {
        return L!.divIcon({
            className: `map-pin map-pin--${variant}`,
            html: '<span aria-hidden="true"></span>',
            iconSize: [28, 28],
            iconAnchor: [14, 28],
            popupAnchor: [0, -26],
        });
    }

    function buildPopup(marker: MapMarker): HTMLElement {
        const wrapper = document.createElement('div');
        wrapper.className = 'map-popup';

        const title = document.createElement('strong');
        title.textContent = marker.title;
        wrapper.appendChild(title);

        if (marker.subtitle) {
            const subtitle = document.createElement('p');
            subtitle.textContent = marker.subtitle;
            wrapper.appendChild(subtitle);
        }

        if (options.onSelect && marker.actionLabel) {
            const button = document.createElement('button');
            button.type = 'button';
            button.textContent = marker.actionLabel;
            button.addEventListener('click', () =>
                options.onSelect?.(marker.id),
            );
            wrapper.appendChild(button);
        }

        return wrapper;
    }

    function setMarkers(markers: MapMarker[]): void {
        if (!L || !markersLayer) {
            pendingMarkers = markers;

            return;
        }

        markersLayer.clearLayers();
        markers.forEach((m) => {
            L!
                .marker([m.latitude, m.longitude], {
                    icon: icon(m.variant),
                    title: m.title,
                    alt: m.title,
                    keyboard: true,
                })
                .bindPopup(buildPopup(m))
                .addTo(markersLayer!);
        });
    }

    /** Mostra a posição do usuário e, opcionalmente, o raio de busca. */
    function setUserLocation(
        coords: Coordinates | null,
        radiusKm?: number,
    ): void {
        if (!L || !userLayer) {
            return;
        }

        userLayer.clearLayers();

        if (!coords) {
            return;
        }

        const latLng: Leaflet.LatLngTuple = [coords.latitude, coords.longitude];
        L.marker(latLng, {
            icon: icon('user'),
            title: 'Você está aqui',
            keyboard: false,
        }).addTo(userLayer);

        if (radiusKm) {
            L.circle(latLng, {
                radius: radiusKm * 1000,
                color: 'var(--primary)',
                weight: 1,
                fillOpacity: 0.06,
            }).addTo(userLayer);
        }
    }

    function setView(coords: Coordinates, zoom?: number): void {
        map.value?.setView(
            [coords.latitude, coords.longitude],
            zoom ?? map.value.getZoom(),
        );
    }

    /** Ajusta o zoom para enquadrar todos os marcadores (e o usuário). */
    function fitToContent(): void {
        if (!L || !map.value || !markersLayer || !userLayer) {
            return;
        }

        const group = L.featureGroup([
            ...markersLayer.getLayers(),
            ...userLayer.getLayers(),
        ]);
        const bounds = group.getBounds();

        if (bounds.isValid()) {
            map.value.fitBounds(bounds, { padding: [32, 32], maxZoom: 15 });
        }
    }

    /** Recalcula o tamanho (necessário quando o container muda de dimensão). */
    function invalidateSize(): void {
        map.value?.invalidateSize();
    }

    onMounted(async () => {
        if (!el.value) {
            return;
        }

        L = await import('leaflet');
        await import('leaflet/dist/leaflet.css');

        const center = options.center ?? DEFAULT_CENTER;
        map.value = L.map(el.value, {
            center: [center.latitude, center.longitude],
            zoom: options.zoom ?? 13,
            scrollWheelZoom: false,
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution:
                '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        }).addTo(map.value);

        markersLayer = L.layerGroup().addTo(map.value);
        userLayer = L.layerGroup().addTo(map.value);

        if (options.onClick) {
            map.value.on('click', (e: Leaflet.LeafletMouseEvent) =>
                options.onClick?.({
                    latitude: Number(e.latlng.lat.toFixed(7)),
                    longitude: Number(e.latlng.lng.toFixed(7)),
                }),
            );
        }

        ready.value = true;

        if (pendingMarkers) {
            setMarkers(pendingMarkers);
            pendingMarkers = null;
        }
    });

    onBeforeUnmount(() => {
        map.value?.remove();
        map.value = null;
    });

    return {
        ready,
        map,
        setMarkers,
        setUserLocation,
        setView,
        fitToContent,
        invalidateSize,
    };
}
