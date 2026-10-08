/**
 * Pesquisa de solicitações com filtros sincronizados à URL.
 *
 * - Os filtros ficam na query string (?q=...&type=...): a busca pode ser
 *   compartilhada, favoritada e sobrevive ao "voltar" do navegador.
 * - Digitação no campo de texto é "debounced" (menos requisições).
 * - Respostas antigas são descartadas (ver useAsync).
 */
import { useDebounceFn } from '@vueuse/core';
import { computed, reactive, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import type { LocationQuery } from 'vue-router';
import { requestService } from '@/services';
import type { HelpRequest, Paginated, RequestFilters } from '@/types';
import { useAsync } from './useAsync';

const FILTER_KEYS = [
    'q',
    'type',
    'category_id',
    'priority',
    'status',
    'radius',
    'sort',
    'page',
] as const;
type FilterKey = (typeof FILTER_KEYS)[number];
export type SearchFilters = Record<FilterKey, string>;

function emptyFilters(): SearchFilters {
    return {
        q: '',
        type: '',
        category_id: '',
        priority: '',
        status: '',
        radius: '',
        sort: 'recent',
        page: '1',
    };
}

function fromQuery(query: LocationQuery): SearchFilters {
    const filters = emptyFilters();
    FILTER_KEYS.forEach((key) => {
        const value = query[key];

        if (typeof value === 'string') {
            filters[key] = value;
        }
    });

    return filters;
}

export interface UseSearchOptions {
    /** Filtros fixos adicionados a toda busca (ex.: { mine: true }). */
    base?: RequestFilters;
    /** Ponto de referência para distância (reativo). */
    origin?: () => { latitude: number; longitude: number } | null;
    debounceMs?: number;
}

export function useSearch(options: UseSearchOptions = {}) {
    const route = useRoute();
    const router = useRouter();
    const filters = reactive<SearchFilters>(fromQuery(route.query));

    const { data, error, loading, execute } = useAsync(
        (params: RequestFilters) => requestService.search(params),
    );

    const results = computed<HelpRequest[]>(() => data.value?.data ?? []);
    const meta = computed<Paginated<HelpRequest>['meta'] | null>(
        () => data.value?.meta ?? null,
    );

    function toParams(): RequestFilters {
        const origin = options.origin?.() ?? null;
        const params: RequestFilters = {
            ...options.base,
            q: filters.q || undefined,
            type: filters.type || undefined,
            category_id: filters.category_id || undefined,
            priority: filters.priority || undefined,
            status: filters.status || undefined,
            sort: (filters.sort || 'recent') as RequestFilters['sort'],
            page: Number(filters.page) || 1,
        };

        if (origin) {
            params.lat = origin.latitude;
            params.lng = origin.longitude;
            params.radius = filters.radius ? Number(filters.radius) : undefined;
        } else if (params.sort === 'distance') {
            params.sort = 'recent';
        }

        return params;
    }

    async function syncUrl(): Promise<void> {
        const query = Object.fromEntries(
            Object.entries(filters).filter(
                ([k, v]) =>
                    v !== '' &&
                    !(k === 'page' && v === '1') &&
                    !(k === 'sort' && v === 'recent'),
            ),
        );
        await router.replace({ query });
    }

    async function search(): Promise<void> {
        await syncUrl();
        await execute(toParams());
    }

    const debouncedSearch = useDebounceFn(search, options.debounceMs ?? 350);

    function goToPage(page: number): void {
        filters.page = String(page);
        void search();
    }

    function reset(): void {
        Object.assign(filters, emptyFilters());
        void search();
    }

    // Texto: debounce. Demais filtros: busca imediata e volta à página 1.
    watch(
        () => filters.q,
        () => {
            filters.page = '1';
            void debouncedSearch();
        },
    );
    watch(
        () => [
            filters.type,
            filters.category_id,
            filters.priority,
            filters.status,
            filters.radius,
            filters.sort,
        ],
        () => {
            filters.page = '1';
            void search();
        },
    );

    const activeFilterCount = computed(
        () =>
            (
                ['type', 'category_id', 'priority', 'status', 'radius'] as const
            ).filter((k) => filters[k] !== '').length,
    );

    return {
        filters,
        results,
        meta,
        loading,
        error,
        search,
        goToPage,
        reset,
        activeFilterCount,
    };
}
