import type {
    ApiResource,
    Organization,
    OrganizationFilters,
    Paginated,
} from '@/types';
import { http } from './http';

export type OrganizationPayload = Partial<
    Omit<Organization, 'id' | 'user_id' | 'status' | 'requests'>
>;

export const organizationService = {
    async list(
        filters: OrganizationFilters = {},
    ): Promise<Paginated<Organization>> {
        const { data } = await http.get<Paginated<Organization>>(
            '/organizations',
            { params: filters },
        );

        return data;
    },

    async get(id: number): Promise<Organization> {
        const { data } = await http.get<ApiResource<Organization>>(
            `/organizations/${id}`,
        );

        return data.data;
    },

    async update(
        id: number,
        payload: OrganizationPayload,
    ): Promise<Organization> {
        const { data } = await http.put<ApiResource<Organization>>(
            `/organizations/${id}`,
            payload,
        );

        return data.data;
    },
};
