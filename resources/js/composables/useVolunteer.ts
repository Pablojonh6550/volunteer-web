/**
 * Perfil do voluntário autenticado e suas atividades.
 */
import { volunteerService } from '@/services';
import type { VolunteerPayload } from '@/services';
import { useAuthStore } from '@/stores/auth';
import { useAsync } from './useAsync';

export function useVolunteer() {
    const auth = useAuthStore();

    const activities = useAsync(
        (params: { status?: string; page?: number } = {}) =>
            volunteerService.activities(params),
    );

    async function update(payload: VolunteerPayload) {
        const id = auth.volunteer?.id;

        if (!id) {
            throw new Error('Usuário não possui perfil de voluntário.');
        }

        const updated = await volunteerService.update(id, payload);
        auth.setVolunteer(updated);

        return updated;
    }

    return { update, activities };
}
