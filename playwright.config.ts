/**
 * Testes E2E (Playwright) contra a aplicação rodando no Docker.
 *
 *   docker compose up -d && docker compose exec app php artisan migrate:fresh --seed
 *   npm run build && npm run test:e2e
 *
 * Base URL configurável por E2E_BASE_URL (padrão http://localhost:8000).
 */
import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
    testDir: './e2e',
    fullyParallel: false,
    retries: process.env.CI ? 1 : 0,
    reporter: process.env.CI ? 'github' : 'list',
    use: {
        baseURL: process.env.E2E_BASE_URL ?? 'http://localhost:8000',
        locale: 'pt-BR',
        trace: 'retain-on-failure',
    },
    projects: [{ name: 'chromium', use: { ...devices['Desktop Chrome'] } }],
});
