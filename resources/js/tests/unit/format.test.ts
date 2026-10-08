import { describe, expect, it } from 'vite-plus/test';
import {
    formatCnpj,
    formatDate,
    formatDistance,
    formatPeriod,
    formatZipCode,
    onlyDigits,
    pluralize,
} from '@/utils/format';

describe('utils/format', () => {
    it('formata datas no padrão brasileiro', () => {
        expect(formatDate('2026-10-05')).toMatch(/05.*out.*2026/);
        expect(formatDate(null)).toBe('—');
        expect(formatDate('invalida')).toBe('—');
    });

    it('descreve períodos parciais', () => {
        expect(formatPeriod(null, null)).toBe('Sem data definida');
        expect(formatPeriod('2026-10-05', null)).toMatch(/^A partir de/);
        expect(formatPeriod(null, '2026-10-05')).toMatch(/^Até/);
    });

    it('formata distâncias em metros e quilômetros', () => {
        expect(formatDistance(0.45)).toBe('450 m');
        expect(formatDistance(3.25)).toBe('3,3 km');
        expect(formatDistance(null)).toBe('');
    });

    it('aplica máscaras de CEP e CNPJ', () => {
        expect(onlyDigits('01.310-100')).toBe('01310100');
        expect(formatZipCode('01310100')).toBe('01310-100');
        expect(formatCnpj('12345678000190')).toBe('12.345.678/0001-90');
    });

    it('pluraliza', () => {
        expect(pluralize(1, 'vaga', 'vagas')).toBe('1 vaga');
        expect(pluralize(3, 'vaga', 'vagas')).toBe('3 vagas');
    });
});
