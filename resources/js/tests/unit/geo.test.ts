import { describe, expect, it } from 'vite-plus/test';
import { DEFAULT_CENTER, hasCoordinates, haversineKm } from '@/utils/geo';

describe('utils/geo', () => {
    it('calcula a distância entre São Paulo e Rio (~360 km)', () => {
        const rio = { latitude: -22.9068, longitude: -43.1729 };
        expect(haversineKm(DEFAULT_CENTER, rio)).toBeGreaterThan(350);
        expect(haversineKm(DEFAULT_CENTER, rio)).toBeLessThan(370);
    });

    it('retorna zero para o mesmo ponto', () => {
        expect(haversineKm(DEFAULT_CENTER, DEFAULT_CENTER)).toBe(0);
    });

    it('identifica objetos com coordenadas', () => {
        expect(hasCoordinates({ latitude: 1, longitude: 2 })).toBe(true);
        expect(hasCoordinates({ latitude: null, longitude: 2 })).toBe(false);
        expect(hasCoordinates(null)).toBe(false);
    });
});
