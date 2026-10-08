/**
 * Formatação para exibição (pt-BR).
 */

const dateFormatter = new Intl.DateTimeFormat('pt-BR', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    timeZone: 'UTC',
});

/** "2026-10-05" ou ISO completo → "05 de out. de 2026". */
export function formatDate(value: string | null | undefined): string {
    if (!value) {
        return '—';
    }

    const date = new Date(value.length === 10 ? `${value}T00:00:00Z` : value);

    return Number.isNaN(date.getTime()) ? '—' : dateFormatter.format(date);
}

/** Intervalo de datas de uma solicitação. */
export function formatPeriod(
    start: string | null | undefined,
    end: string | null | undefined,
): string {
    if (!start && !end) {
        return 'Sem data definida';
    }

    if (start && end) {
        return `${formatDate(start)} – ${formatDate(end)}`;
    }

    return start
        ? `A partir de ${formatDate(start)}`
        : `Até ${formatDate(end)}`;
}

/** Distância em km → "850 m" / "3,2 km". */
export function formatDistance(km: number | null | undefined): string {
    if (km === null || km === undefined) {
        return '';
    }

    if (km < 1) {
        return `${Math.round(km * 1000)} m`;
    }

    return `${km.toLocaleString('pt-BR', { maximumFractionDigits: 1 })} km`;
}

/** Remove tudo que não é dígito (CEP, CNPJ, telefone). */
export function onlyDigits(value: string | null | undefined): string {
    return (value ?? '').replace(/\D/g, '');
}

/** "01310100" → "01310-100". */
export function formatZipCode(value: string | null | undefined): string {
    const digits = onlyDigits(value).slice(0, 8);

    return digits.length > 5
        ? `${digits.slice(0, 5)}-${digits.slice(5)}`
        : digits;
}

/** "12345678000190" → "12.345.678/0001-90". */
export function formatCnpj(value: string | null | undefined): string {
    const d = onlyDigits(value).slice(0, 14);

    return d
        .replace(/^(\d{2})(\d)/, '$1.$2')
        .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
        .replace(/\.(\d{3})(\d)/, '.$1/$2')
        .replace(/(\d{4})(\d)/, '$1-$2');
}

/** Pluralização simples: pluralize(2, 'vaga', 'vagas') → "2 vagas". */
export function pluralize(
    count: number,
    singular: string,
    plural: string,
): string {
    return `${count} ${count === 1 ? singular : plural}`;
}
