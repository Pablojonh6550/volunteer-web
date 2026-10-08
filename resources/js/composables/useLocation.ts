/**
 * Geolocalização do navegador.
 *
 * O estado é compartilhado entre componentes (módulo singleton) para não
 * pedir a localização repetidamente. A localização só é solicitada por ação
 * explícita do usuário (clique em "usar minha localização") — nunca
 * automaticamente — respeitando o consentimento (LGPD).
 */
import { computed, readonly, ref } from 'vue';
import type { Coordinates } from '@/types';

const coords = ref<Coordinates | null>(null);
const error = ref<string | null>(null);
const loading = ref(false);

const MESSAGES: Record<number, string> = {
    1: 'Permissão de localização negada. Você pode informar o endereço manualmente.',
    2: 'Não foi possível determinar sua localização.',
    3: 'A busca pela localização demorou demais. Tente novamente.',
};

export function useLocation() {
    const supported =
        typeof navigator !== 'undefined' && 'geolocation' in navigator;

    function locate(): Promise<Coordinates | null> {
        if (!supported) {
            error.value = 'Seu navegador não oferece geolocalização.';

            return Promise.resolve(null);
        }

        loading.value = true;
        error.value = null;

        return new Promise((resolve) => {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    coords.value = {
                        latitude: Number(position.coords.latitude.toFixed(7)),
                        longitude: Number(position.coords.longitude.toFixed(7)),
                    };
                    loading.value = false;
                    resolve(coords.value);
                },
                (err) => {
                    error.value = MESSAGES[err.code] ?? MESSAGES[2];
                    loading.value = false;
                    resolve(null);
                },
                {
                    enableHighAccuracy: false,
                    timeout: 10000,
                    maximumAge: 5 * 60 * 1000,
                },
            );
        });
    }

    /** Define manualmente (ex.: clique no mapa ou localização salva no perfil). */
    function setCoords(value: Coordinates | null): void {
        coords.value = value;
    }

    return {
        coords: readonly(coords),
        hasLocation: computed(() => coords.value !== null),
        error: readonly(error),
        loading: readonly(loading),
        supported,
        locate,
        setCoords,
    };
}
