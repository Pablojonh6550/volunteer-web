/**
 * Cliente HTTP único da aplicação (axios).
 *
 * Autenticação: Sanctum SPA (cookie de sessão HttpOnly + token CSRF).
 *  - `withCredentials` envia os cookies de sessão.
 *  - `withXSRFToken` copia o cookie XSRF-TOKEN para o header X-XSRF-TOKEN.
 *  - Antes de login/cadastro chamamos `ensureCsrfCookie()`.
 *
 * Todos os erros são normalizados para `ApiError` (status, message, errors),
 * para que páginas e composables tratem falhas de forma uniforme.
 */
import axios, { AxiosError } from 'axios';
import type { AxiosRequestConfig } from 'axios';
import type { ApiError, ValidationErrorBody } from '@/types';

export const http = axios.create({
    baseURL: '/api',
    withCredentials: true,
    withXSRFToken: true,
    headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

/** Obtém o cookie CSRF do Sanctum (necessário antes de requisições que alteram estado). */
export async function ensureCsrfCookie(): Promise<void> {
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true });
}

type UnauthorizedHandler = () => void;
let unauthorizedHandler: UnauthorizedHandler | null = null;

/**
 * Registra o que fazer quando a sessão expira (HTTP 401).
 * Definido pela store de autenticação — evita import circular.
 */
export function onUnauthorized(handler: UnauthorizedHandler): void {
    unauthorizedHandler = handler;
}

const GENERIC_MESSAGES: Record<number, string> = {
    0: 'Não foi possível conectar ao servidor. Verifique sua conexão.',
    401: 'Sua sessão expirou. Entre novamente.',
    403: 'Você não tem permissão para realizar esta ação.',
    404: 'O recurso solicitado não foi encontrado.',
    419: 'Sua sessão expirou. Recarregue a página.',
    429: 'Muitas requisições. Aguarde um instante e tente novamente.',
    500: 'Ocorreu um erro inesperado. Tente novamente mais tarde.',
};

/** Converte qualquer erro em ApiError. */
export function toApiError(error: unknown): ApiError {
    if (error instanceof AxiosError) {
        const status = error.response?.status ?? 0;
        const body = error.response?.data as
            | Partial<ValidationErrorBody>
            | undefined;

        // Em 5xx a mensagem do servidor pode conter detalhes internos: usamos a genérica.
        const serverMessage = status < 500 ? body?.message : undefined;

        return {
            status,
            message:
                serverMessage ||
                GENERIC_MESSAGES[status] ||
                GENERIC_MESSAGES[500],
            errors: body?.errors ?? {},
        };
    }

    return { status: 0, message: GENERIC_MESSAGES[500], errors: {} };
}

export function isApiError(value: unknown): value is ApiError {
    return (
        typeof value === 'object' &&
        value !== null &&
        'status' in value &&
        'message' in value &&
        'errors' in value
    );
}

http.interceptors.response.use(
    (response) => response,
    async (error: AxiosError) => {
        const config = error.config as
            | (AxiosRequestConfig & { _retried?: boolean })
            | undefined;

        // Token CSRF expirado: renova uma única vez e repete a requisição.
        if (error.response?.status === 419 && config && !config._retried) {
            config._retried = true;
            await ensureCsrfCookie();

            return http.request(config);
        }

        if (error.response?.status === 401) {
            unauthorizedHandler?.();
        }

        return Promise.reject(toApiError(error));
    },
);
