import type { Page } from '@playwright/test';

/** Gera e-mails únicos para que os testes possam rodar várias vezes no mesmo banco. */
export const uniqueEmail = (prefix: string) =>
    `${prefix}.${Date.now()}.${Math.floor(Math.random() * 1000)}@e2e.test`;

export async function register(
    page: Page,
    data: {
        type: 'volunteer' | 'organization';
        name: string;
        email: string;
        organizationName?: string;
    },
): Promise<void> {
    await page.goto(`/cadastro?tipo=${data.type}`);

    if (data.organizationName) {
        await page.getByLabel('Nome da ONG').fill(data.organizationName);
    }

    await page
        .getByLabel(
            data.type === 'organization'
                ? 'Nome do responsável'
                : 'Nome completo',
        )
        .fill(data.name);
    await page.getByLabel('E-mail').fill(data.email);
    await page
        .getByLabel('Senha', { exact: false })
        .first()
        .fill('password123');
    await page.getByLabel('Confirme a senha').fill('password123');
    await page.getByRole('checkbox', { name: /termos de uso/ }).check();
    await page.getByRole('button', { name: 'Criar conta' }).click();
}

export async function login(
    page: Page,
    email: string,
    password = 'password',
): Promise<void> {
    await page.goto('/entrar');
    await page.getByLabel('E-mail').fill(email);
    await page.getByLabel('Senha').fill(password);
    await page.getByRole('button', { name: 'Entrar' }).click();
}

export async function logout(page: Page): Promise<void> {
    await page.getByRole('button', { name: 'Menu da conta' }).click();
    await page.getByRole('menuitem', { name: 'Sair' }).click();
    await page.waitForURL('**/entrar');
}
