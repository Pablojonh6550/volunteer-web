/**
 * Categorias de solicitações. Carregadas uma vez por sessão e reutilizadas
 * em filtros, formulários e perfil (cache em memória).
 */
import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { catalogService } from '@/services';
import type { Category, RequestType } from '@/types';

export const useCategoriesStore = defineStore('categories', () => {
    const items = ref<Category[]>([]);
    const loaded = ref(false);
    const loading = ref(false);
    let pending: Promise<Category[]> | null = null;

    const byType = computed(() => {
        const groups: Record<RequestType, Category[]> = {
            donation: [],
            service: [],
            volunteering: [],
        };
        items.value.forEach((c) => groups[c.type].push(c));

        return groups;
    });

    async function load(force = false): Promise<Category[]> {
        if (loaded.value && !force) {
            return items.value;
        }

        loading.value = true;
        pending ??= catalogService
            .categories()
            .then((list) => {
                items.value = list;
                loaded.value = true;

                return list;
            })
            .finally(() => {
                loading.value = false;
                pending = null;
            });

        return pending;
    }

    function forType(type?: RequestType | '' | null): Category[] {
        return type ? byType.value[type] : items.value;
    }

    return { items, loaded, loading, byType, load, forType };
});
