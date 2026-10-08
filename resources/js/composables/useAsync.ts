/**
 * Estado assíncrono genérico: loading, erro e dados de uma operação.
 * Base para os composables de domínio (useRequest, useOrganization...).
 */
import { ref, shallowRef } from 'vue';
import type { Ref } from 'vue';
import { isApiError, toApiError } from '@/services/http';
import type { ApiError } from '@/types';

export interface UseAsyncReturn<T, A extends unknown[]> {
    data: Ref<T | null>;
    error: Ref<ApiError | null>;
    loading: Ref<boolean>;
    execute: (...args: A) => Promise<T | null>;
}

export function useAsync<T, A extends unknown[] = []>(
    fn: (...args: A) => Promise<T>,
    options: { immediate?: boolean; initialData?: T | null } = {},
): UseAsyncReturn<T, A> {
    const data = shallowRef<T | null>(
        options.initialData ?? null,
    ) as Ref<T | null>;
    const error = ref<ApiError | null>(null);
    const loading = ref(false);
    // Ignora respostas de chamadas antigas (evita condição de corrida em buscas).
    let callId = 0;

    async function execute(...args: A): Promise<T | null> {
        const id = ++callId;
        loading.value = true;
        error.value = null;

        try {
            const result = await fn(...args);

            if (id === callId) {
                data.value = result;
            }

            return result;
        } catch (e) {
            if (id === callId) {
                error.value = isApiError(e) ? e : toApiError(e);
            }

            return null;
        } finally {
            if (id === callId) {
                loading.value = false;
            }
        }
    }

    if (options.immediate) {
        void execute(...([] as unknown as A));
    }

    return { data, error, loading, execute };
}
