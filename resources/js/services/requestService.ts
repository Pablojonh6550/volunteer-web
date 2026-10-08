/**
 * Solicitações (doações, serviços e voluntariado) e fluxo de interesse.
 */
import type {
    ApiResource,
    HelpRequest,
    Paginated,
    RequestFilters,
    RequestPriority,
    RequestStatus,
    RequestType,
    RequestVolunteer,
    RequestVolunteerStatus,
} from '@/types';
import { http } from './http';

export interface HelpRequestPayload {
    type: RequestType;
    category_id: number | null;
    title: string;
    description: string;
    quantity: number | null;
    priority: RequestPriority;
    start_date: string | null;
    end_date: string | null;
    latitude: number | null;
    longitude: number | null;
}

export interface DeleteResult {
    deleted: boolean;
    message: string;
    data: HelpRequest | null;
}

/** Remove filtros vazios para não poluir a query string. */
function cleanParams(filters: RequestFilters): Record<string, unknown> {
    return Object.fromEntries(
        Object.entries(filters)
            .filter(([, v]) => v !== undefined && v !== null && v !== '')
            .map(([k, v]) => [k, typeof v === 'boolean' ? Number(v) : v]),
    );
}

export const requestService = {
    async search(
        filters: RequestFilters = {},
    ): Promise<Paginated<HelpRequest>> {
        const { data } = await http.get<Paginated<HelpRequest>>('/requests', {
            params: cleanParams(filters),
        });

        return data;
    },

    async get(id: number): Promise<HelpRequest> {
        const { data } = await http.get<ApiResource<HelpRequest>>(
            `/requests/${id}`,
        );

        return data.data;
    },

    async create(payload: HelpRequestPayload): Promise<HelpRequest> {
        const { data } = await http.post<ApiResource<HelpRequest>>(
            '/requests',
            payload,
        );

        return data.data;
    },

    async update(
        id: number,
        payload: Partial<HelpRequestPayload> & { status?: RequestStatus },
    ): Promise<HelpRequest> {
        const { data } = await http.put<ApiResource<HelpRequest>>(
            `/requests/${id}`,
            payload,
        );

        return data.data;
    },

    async remove(id: number): Promise<DeleteResult> {
        const { data } = await http.delete<DeleteResult>(`/requests/${id}`);

        return data;
    },

    async expressInterest(
        id: number,
        message?: string,
    ): Promise<RequestVolunteer> {
        const { data } = await http.post<ApiResource<RequestVolunteer>>(
            `/requests/${id}/interest`,
            { message },
        );

        return data.data;
    },

    async cancelInterest(id: number): Promise<RequestVolunteer> {
        const { data } = await http.delete<ApiResource<RequestVolunteer>>(
            `/requests/${id}/interest`,
        );

        return data.data;
    },

    async volunteers(
        id: number,
        status?: RequestVolunteerStatus,
    ): Promise<RequestVolunteer[]> {
        const { data } = await http.get<ApiResource<RequestVolunteer[]>>(
            `/requests/${id}/volunteers`,
            { params: status ? { status } : {} },
        );

        return data.data;
    },

    async reviewParticipation(
        participationId: number,
        status: Extract<
            RequestVolunteerStatus,
            'approved' | 'rejected' | 'completed'
        >,
    ): Promise<RequestVolunteer> {
        const { data } = await http.patch<ApiResource<RequestVolunteer>>(
            `/request-volunteers/${participationId}`,
            { status },
        );

        return data.data;
    },
};
