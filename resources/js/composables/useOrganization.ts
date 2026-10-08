/**
 * Perfil de ONG: carregar (própria ou de terceiros) e atualizar a própria.
 */
import { organizationService } from '@/services';
import type { OrganizationPayload } from '@/services';
import { useAuthStore } from '@/stores/auth';
import { useAsync } from './useAsync';

export function useOrganization() {
    const auth = useAuthStore();
    const {
        data: organization,
        error,
        loading,
        execute,
    } = useAsync((id: number) => organizationService.get(id));

    async function update(payload: OrganizationPayload) {
        const id = auth.organization?.id;

        if (!id) {
            throw new Error('Usuário não possui ONG.');
        }

        const updated = await organizationService.update(id, payload);
        auth.setOrganization(updated);
        organization.value = updated;

        return updated;
    }

    return { organization, error, loading, load: execute, update };
}
