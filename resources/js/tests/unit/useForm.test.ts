import { describe, expect, it } from 'vite-plus/test';
import { useForm } from '@/composables/useForm';

describe('useForm', () => {
    it('controla processing e devolve o resultado em caso de sucesso', async () => {
        const form = useForm({ email: '' });
        const promise = form.submit(async () => 'ok');

        expect(form.processing.value).toBe(true);
        await expect(promise).resolves.toBe('ok');
        expect(form.processing.value).toBe(false);
        expect(form.recentlySuccessful.value).toBe(true);
    });

    it('mapeia erros 422 para os campos', async () => {
        const form = useForm({ email: '', category_ids: [] as number[] });

        const result = await form.submit(() =>
            Promise.reject({
                status: 422,
                message: 'Inválido',
                errors: {
                    email: ['Obrigatório'],
                    'category_ids.0': ['Categoria inválida'],
                },
            }),
        );

        expect(result).toBeNull();
        expect(form.errors.email).toBe('Obrigatório');
        expect(form.errors.category_ids).toBe('Categoria inválida');
        expect(form.message.value).toBe('Inválido');
    });

    it('reseta dados e erros', async () => {
        const form = useForm({ name: 'A' });
        form.data.name = 'B';
        await form.submit(() =>
            Promise.reject({
                status: 422,
                message: 'x',
                errors: { name: ['erro'] },
            }),
        );

        form.reset();

        expect(form.data.name).toBe('A');
        expect(form.hasErrors()).toBe(false);
    });
});
