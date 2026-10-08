import type {
    ApiResource,
    Category,
    OrganizationDashboard,
    VolunteerDashboard,
} from '@/types';
import { http } from './http';

export const catalogService = {
    async categories(): Promise<Category[]> {
        const { data } = await http.get<ApiResource<Category[]>>('/categories');

        return data.data;
    },

    async dashboard<
        T extends OrganizationDashboard | VolunteerDashboard,
    >(): Promise<T> {
        const { data } = await http.get<ApiResource<T>>('/dashboard');

        return data.data;
    },
};
