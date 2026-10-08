/**
 * Feedback ao usuário via toasts (vue-sonner). Os toasts são anunciados
 * por leitores de tela (região aria-live do Toaster).
 */
import { toast } from 'vue-sonner';
import { isApiError } from '@/services/http';

export function useNotification() {
    function success(message: string, description?: string): void {
        toast.success(message, { description });
    }

    function info(message: string, description?: string): void {
        toast.info(message, { description });
    }

    function error(
        messageOrError: unknown,
        fallback = 'Algo deu errado.',
    ): void {
        const message =
            typeof messageOrError === 'string'
                ? messageOrError
                : isApiError(messageOrError)
                  ? messageOrError.message
                  : fallback;

        toast.error(message);
    }

    return { success, info, error };
}
