/**
 * Fluxo principal do MVP, ponta a ponta:
 * ONG publica → voluntário encontra e demonstra interesse → ONG aprova → ONG conclui.
 */
import { expect, test } from '@playwright/test';
import { logout, register, uniqueEmail } from './helpers';

test('fluxo completo de interesse e aprovação', async ({ page }) => {
    const title = `Mutirão E2E ${Date.now()}`;
    const orgEmail = uniqueEmail('ong');
    const volunteerEmail = uniqueEmail('vol');

    // 1. ONG se cadastra e publica uma solicitação.
    await register(page, {
        type: 'organization',
        name: 'Responsável E2E',
        organizationName: 'ONG E2E',
        email: orgEmail,
    });
    await expect(
        page.getByRole('heading', { name: 'Perfil da ONG' }),
    ).toBeVisible();

    await page.getByRole('link', { name: 'Nova solicitação' }).first().click();
    await page.getByLabel('Tipo').selectOption('volunteering');
    await page.getByLabel('Título').fill(title);
    await page
        .getByLabel('Descrição')
        .fill(
            'Precisamos de voluntários para o mutirão de limpeza da praça no sábado.',
        );
    await page.getByRole('button', { name: 'Publicar solicitação' }).click();
    await expect(
        page.getByRole('heading', { level: 1, name: title }),
    ).toBeVisible();
    await logout(page);

    // 2. Voluntário se cadastra, pesquisa e demonstra interesse.
    await register(page, {
        type: 'volunteer',
        name: 'Voluntário E2E',
        email: volunteerEmail,
    });
    await expect(
        page.getByRole('heading', { name: 'Meu perfil' }),
    ).toBeVisible();

    await page
        .getByRole('link', { name: 'Pesquisar solicitações' })
        .first()
        .click();
    await page.getByLabel('Buscar').fill(title);
    await page.getByRole('link', { name: title }).click();
    await page
        .getByLabel('Mensagem para a ONG (opcional)')
        .fill('Posso ajudar a manhã toda.');
    await page.getByRole('button', { name: 'Quero ajudar' }).click();
    await expect(
        page.getByText('A ONG vai analisar seu interesse'),
    ).toBeVisible();
    await logout(page);

    // 3. ONG vê o interessado (sem contato) e aprova → contato liberado.
    await page.goto('/entrar');
    await page.getByLabel('E-mail').fill(orgEmail);
    await page.getByLabel('Senha').fill('password123');
    await page.getByRole('button', { name: 'Entrar' }).click();

    await page.getByRole('link', { name: 'Solicitações', exact: true }).click();
    await page.getByRole('link', { name: title }).click();
    await page.getByRole('link', { name: /Interessados \(1\)/ }).click();
    await expect(page.getByText('Posso ajudar a manhã toda.')).toBeVisible();
    await expect(page.getByText(volunteerEmail)).toHaveCount(0);

    await page.getByRole('button', { name: 'Aprovar Voluntário E2E' }).click();
    await expect(page.getByText(volunteerEmail)).toBeVisible();

    // 4. ONG conclui a solicitação.
    await page.getByRole('link', { name: 'Voltar à solicitação' }).click();
    await page.getByRole('button', { name: 'Concluir' }).click();
    await page
        .getByRole('dialog')
        .getByRole('button', { name: 'Concluir' })
        .click();
    await expect(page.getByText('Concluída').first()).toBeVisible();
    await logout(page);

    // 5. Voluntário vê a atividade concluída no histórico.
    await page.goto('/entrar');
    await page.getByLabel('E-mail').fill(volunteerEmail);
    await page.getByLabel('Senha').fill('password123');
    await page.getByRole('button', { name: 'Entrar' }).click();
    await page.getByRole('link', { name: 'Minhas atividades' }).click();
    await expect(
        page.getByRole('listitem').filter({ hasText: title }),
    ).toContainText('Concluído');
});

test('voluntário não acessa telas de ONG', async ({ page }) => {
    await register(page, {
        type: 'volunteer',
        name: 'Curioso',
        email: uniqueEmail('curioso'),
    });
    await expect(
        page.getByRole('heading', { name: 'Meu perfil' }),
    ).toBeVisible();
    await page.goto('/ong/solicitacoes/nova');

    await expect(page).toHaveURL(/\/voluntario$/);
});
