/**
 * Setup global dos testes Vitest (ambiente jsdom).
 */
import { config } from '@vue/test-utils';
import { RouterLinkStub } from '@vue/test-utils';
import { afterEach, vi } from 'vite-plus/test';

// RouterLink vira um stub simples: componentes podem ser montados sem router real.
config.global.stubs = { RouterLink: RouterLinkStub };

// jsdom não implementa matchMedia (usado por useAppearance).
Object.defineProperty(window, 'matchMedia', {
    writable: true,
    value: vi.fn().mockImplementation((query: string) => ({
        matches: false,
        media: query,
        addEventListener: vi.fn(),
        removeEventListener: vi.fn(),
    })),
});

afterEach(() => {
    vi.restoreAllMocks();
});
