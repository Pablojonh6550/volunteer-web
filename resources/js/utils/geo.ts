/**
 * Utilidades geográficas do lado do cliente.
 * O filtro "oficial" por distância é feito na API (Haversine em SQL); estas
 * funções servem para feedback imediato na interface (ex.: mapa).
 */
import type { Coordinates } from '@/types';

/** Centro padrão do mapa quando não há localização (Praça da Sé, São Paulo). */
export const DEFAULT_CENTER: Coordinates = {
    latitude: -23.5505,
    longitude: -46.6333,
};

export const RADIUS_OPTIONS = [2, 5, 10, 25, 50] as const;

const EARTH_RADIUS_KM = 6371;

const toRad = (deg: number) => (deg * Math.PI) / 180;

/** Distância em km entre dois pontos (fórmula de Haversine). */
export function haversineKm(a: Coordinates, b: Coordinates): number {
    const dLat = toRad(b.latitude - a.latitude);
    const dLng = toRad(b.longitude - a.longitude);
    const h =
        Math.sin(dLat / 2) ** 2 +
        Math.cos(toRad(a.latitude)) *
            Math.cos(toRad(b.latitude)) *
            Math.sin(dLng / 2) ** 2;

    return 2 * EARTH_RADIUS_KM * Math.asin(Math.sqrt(h));
}

/** Type guard: o objeto possui latitude/longitude válidas. */
export function hasCoordinates<
    T extends { latitude?: number | null; longitude?: number | null },
>(value: T | null | undefined): value is T & Coordinates {
    return (
        value !== null &&
        value !== undefined &&
        typeof value.latitude === 'number' &&
        typeof value.longitude === 'number'
    );
}
