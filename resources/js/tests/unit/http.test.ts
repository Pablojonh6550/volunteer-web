import { AxiosError, AxiosHeaders } from 'axios';
import { describe, expect, it } from 'vite-plus/test';
import { isApiError, toApiError } from '@/services/http';

function axiosError(status: number, data: unknown): AxiosError {
    return new AxiosError('fail', 'ERR', undefined, undefined, {
        status,
        data,
        statusText: '',
        headers: {},
        config: { headers: new AxiosHeaders() },
    });
}

describe('services/http', () => {
    it('normaliza erros de validação do Laravel (422)', () => {
        const error = toApiError(
            axiosError(422, {
                message: 'Dados inválidos',
                errors: { email: ['E-mail inválido'] },
            }),
        );

        expect(error).toEqual({
            status: 422,
            message: 'Dados inválidos',
            errors: { email: ['E-mail inválido'] },
        });
        expect(isApiError(error)).toBe(true);
    });

    it('não expõe mensagens internas de erros 5xx', () => {
        const error = toApiError(
            axiosError(500, { message: 'SQLSTATE[42S02] table not found' }),
        );

        expect(error.message).not.toContain('SQLSTATE');
        expect(error.status).toBe(500);
    });

    it('usa mensagem amigável para falhas de rede', () => {
        expect(toApiError(new AxiosError('Network Error')).message).toMatch(
            /conectar/,
        );
    });
});
