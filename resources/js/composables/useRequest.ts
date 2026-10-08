/**
 * Uma solicitação e suas ações (detalhe, interesse, status, exclusão).
 */
import { ref } from 'vue';
import { requestService } from '@/services';
import type { HelpRequest, RequestStatus } from '@/types';
import { useAsync } from './useAsync';
import { useNotification } from './useNotification';

export function useRequest() {
    const notify = useNotification();
    const {
        data: request,
        error,
        loading,
        execute,
    } = useAsync((id: number) => requestService.get(id));
    const acting = ref(false);

    async function run<T>(
        action: () => Promise<T>,
        successMessage?: string,
    ): Promise<T | null> {
        acting.value = true;

        try {
            const result = await action();

            if (successMessage) {
                notify.success(successMessage);
            }

            return result;
        } catch (e) {
            notify.error(e);

            return null;
        } finally {
            acting.value = false;
        }
    }

    const load = (id: number) => execute(id);

    async function expressInterest(message?: string): Promise<boolean> {
        const current = request.value;

        if (!current) {
            return false;
        }

        const participation = await run(
            () => requestService.expressInterest(current.id, message),
            'Interesse enviado! A ONG foi avisada.',
        );

        if (participation) {
            request.value = {
                ...current,
                my_participation: participation,
                interested_count: (current.interested_count ?? 0) + 1,
            };
        }

        return participation !== null;
    }

    async function cancelInterest(): Promise<boolean> {
        const current = request.value;

        if (!current) {
            return false;
        }

        const participation = await run(
            () => requestService.cancelInterest(current.id),
            'Interesse cancelado.',
        );

        if (participation) {
            request.value = {
                ...current,
                my_participation: participation,
                interested_count: Math.max(
                    (current.interested_count ?? 1) - 1,
                    0,
                ),
            };
        }

        return participation !== null;
    }

    async function updateStatus(
        status: RequestStatus,
        successMessage: string,
    ): Promise<HelpRequest | null> {
        const current = request.value;

        if (!current) {
            return null;
        }

        const updated = await run(
            () => requestService.update(current.id, { status }),
            successMessage,
        );

        if (updated) {
            request.value = { ...current, ...updated };
        }

        return updated;
    }

    async function remove(): Promise<boolean> {
        const current = request.value;

        if (!current) {
            return false;
        }

        const result = await run(() => requestService.remove(current.id));

        if (result) {
            notify.success(result.message);

            if (result.data) {
                request.value = { ...current, ...result.data };
            }
        }

        return result?.deleted ?? false;
    }

    return {
        request,
        error,
        loading,
        acting,
        load,
        expressInterest,
        cancelInterest,
        updateStatus,
        remove,
    };
}
