import { describe, expect, it } from 'vite-plus/test';
import { useAsync } from '@/composables/useAsync';

describe('useAsync', () => {
    it('guarda dados e controla loading', async () => {
        const state = useAsync((n: number) => Promise.resolve(n * 2));

        await state.execute(21);

        expect(state.data.value).toBe(42);
        expect(state.loading.value).toBe(false);
        expect(state.error.value).toBeNull();
    });

    it('descarta respostas antigas (condição de corrida)', async () => {
        let resolveSlow: (v: string) => void = () => {};
        const calls = [
            new Promise<string>((r) => (resolveSlow = r)),
            Promise.resolve('rápida'),
        ];
        const state = useAsync(() => calls.shift()!);

        const slow = state.execute();
        await state.execute();
        resolveSlow('lenta');
        await slow;

        expect(state.data.value).toBe('rápida');
    });

    it('normaliza erros', async () => {
        const state = useAsync(() => Promise.reject(new Error('boom')));

        await state.execute();

        expect(state.error.value?.status).toBe(0);
        expect(state.data.value).toBeNull();
    });
});
