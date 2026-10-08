/**
 * Enums do domínio — espelham app/Enums/*.php no backend.
 *
 * Mantidos como union types (e não `enum` do TS) para serem apagados na
 * compilação e comparáveis diretamente com os valores vindos da API.
 */

export type UserType = 'organization' | 'volunteer';

export type RequestType = 'donation' | 'service' | 'volunteering';

export type RequestStatus = 'open' | 'in_progress' | 'completed' | 'cancelled';

export type RequestPriority = 'low' | 'medium' | 'high' | 'urgent';

export type RequestVolunteerStatus =
    | 'interested'
    | 'approved'
    | 'rejected'
    | 'cancelled'
    | 'completed';

export type OrganizationStatus = 'active' | 'inactive';

export const USER_TYPE_LABELS: Record<UserType, string> = {
    organization: 'ONG',
    volunteer: 'Voluntário',
};

export const REQUEST_TYPE_LABELS: Record<RequestType, string> = {
    donation: 'Doação',
    service: 'Serviço',
    volunteering: 'Voluntariado',
};

export const REQUEST_STATUS_LABELS: Record<RequestStatus, string> = {
    open: 'Aberta',
    in_progress: 'Em andamento',
    completed: 'Concluída',
    cancelled: 'Cancelada',
};

export const REQUEST_PRIORITY_LABELS: Record<RequestPriority, string> = {
    low: 'Baixa',
    medium: 'Média',
    high: 'Alta',
    urgent: 'Urgente',
};

export const REQUEST_VOLUNTEER_STATUS_LABELS: Record<
    RequestVolunteerStatus,
    string
> = {
    interested: 'Interessado',
    approved: 'Aprovado',
    rejected: 'Recusado',
    cancelled: 'Cancelado',
    completed: 'Concluído',
};

/** Transforma um mapa de rótulos em opções para <select>. */
export function toOptions<T extends string>(
    labels: Record<T, string>,
): { value: T; label: string }[] {
    return (Object.keys(labels) as T[]).map((value) => ({
        value,
        label: labels[value],
    }));
}
