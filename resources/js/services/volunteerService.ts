import type {
    ApiResource,
    Paginated,
    RequestVolunteer,
    Volunteer,
} from '@/types';
import { http } from './http';

export interface VolunteerPayload {
    birth_date?: string | null;
    phone?: string | null;
    bio?: string | null;
    available?: boolean;
    location_consent?: boolean;
    category_ids?: number[];
    address?: string | null;
    number?: string | null;
    neighborhood?: string | null;
    city?: string | null;
    state?: string | null;
    zip_code?: string | null;
    latitude?: number | null;
    longitude?: number | null;
}

export const volunteerService = {
    async get(id: number): Promise<Volunteer> {
        const { data } = await http.get<ApiResource<Volunteer>>(
            `/volunteers/${id}`,
        );

        return data.data;
    },

    async update(id: number, payload: VolunteerPayload): Promise<Volunteer> {
        const { data } = await http.put<ApiResource<Volunteer>>(
            `/volunteers/${id}`,
            payload,
        );

        return data.data;
    },

    /** "Minhas atividades" do voluntário autenticado. */
    async activities(
        params: { status?: string; page?: number } = {},
    ): Promise<Paginated<RequestVolunteer>> {
        const { data } = await http.get<Paginated<RequestVolunteer>>(
            '/me/activities',
            { params },
        );

        return data;
    },
};
