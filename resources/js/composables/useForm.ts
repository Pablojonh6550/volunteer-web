/**
 * Estado de formulário com integração aos erros de validação do Laravel (422).
 *
 * const form = useForm({ email: '', password: '' });
 * await form.submit(() => authService.login(form.data));
 * form.errors.email // "As credenciais informadas são inválidas."
 */
import { reactive, ref } from 'vue';
import type { UnwrapRef } from 'vue';
import { isApiError, toApiError } from '@/services/http';
import type { ApiError } from '@/types';

export function useForm<T extends Record<string, unknown>>(initial: T) {
    const defaults = structuredClone(initial);
    const data = reactive<T>(structuredClone(initial));
    const errors = reactive<Record<string, string>>({});
    const processing = ref(false);
    const recentlySuccessful = ref(false);
    const message = ref<string | null>(null);

    function setErrors(error: ApiError): void {
        clearErrors();
        message.value = error.message;
        Object.entries(error.errors).forEach(([field, list]) => {
            // "category_ids.0" → também marca o campo "category_ids".
            errors[field] = list[0];
            const root = field.split('.')[0];
            errors[root] ??= list[0];
        });
    }

    function clearErrors(field?: string): void {
        if (field) {
            delete errors[field];

            return;
        }

        Object.keys(errors).forEach((key) => delete errors[key]);
        message.value = null;
    }

    function reset(values?: Partial<T>): void {
        Object.assign(data, structuredClone(defaults), values ?? {});
        clearErrors();
    }

    /**
     * Executa `action`, controlando `processing` e populando `errors` em falhas.
     * Retorna o resultado, ou `null` em caso de erro.
     */
    async function submit<R>(action: () => Promise<R>): Promise<R | null> {
        processing.value = true;
        recentlySuccessful.value = false;
        clearErrors();

        try {
            const result = await action();
            recentlySuccessful.value = true;
            setTimeout(() => (recentlySuccessful.value = false), 2000);

            return result;
        } catch (e) {
            setErrors(isApiError(e) ? e : toApiError(e));

            return null;
        } finally {
            processing.value = false;
        }
    }

    const hasErrors = () =>
        Object.keys(errors).length > 0 || message.value !== null;

    return {
        data: data as UnwrapRef<T>,
        errors,
        message,
        processing,
        recentlySuccessful,
        submit,
        reset,
        setErrors,
        clearErrors,
        hasErrors,
    };
}
