import { expect, test } from '@playwright/test';

test('home pública apresenta a plataforma e leva ao cadastro', async ({
    page,
}) => {
    await page.goto('/');

    await expect(page.getByRole('heading', { level: 1 })).toContainText(
        'Conectamos ONGs',
    );
    await page.getByRole('link', { name: 'Sou uma ONG' }).click();

    await expect(page).toHaveURL(/cadastro\?tipo=organization/);
    await expect(page.getByLabel('Nome da ONG')).toBeVisible();
});

test('rotas protegidas redirecionam para o login preservando o destino', async ({
    page,
}) => {
    await page.goto('/voluntario/atividades');

    await expect(page).toHaveURL(/entrar\?redirect=/);
});

test('link "pular para o conteúdo" é o primeiro foco da página', async ({
    page,
}) => {
    await page.goto('/');
    await page.keyboard.press('Tab');

    await expect(
        page.getByRole('link', { name: 'Pular para o conteúdo' }),
    ).toBeFocused();
});

test('login inválido exibe erro acessível', async ({ page }) => {
    await page.goto('/entrar');
    await page.getByLabel('E-mail').fill('ninguem@example.com');
    await page.getByLabel('Senha').fill('errada');
    await page.getByRole('button', { name: 'Entrar' }).click();

    await expect(page.getByRole('alert')).toBeVisible();
});
