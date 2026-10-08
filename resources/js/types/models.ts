/**
 * Contratos dos recursos retornados pela API (app/Http/Resources/*).
 */
import type {
    OrganizationStatus,
    RequestPriority,
    RequestStatus,
    RequestType,
    RequestVolunteerStatus,
    UserType,
} from './enums';

export interface Coordinates {
    latitude: number;
    longitude: number;
}

export interface Address {
    address: string | null;
    number: string | null;
    neighborhood: string | null;
    city: string | null;
    state: string | null;
    zip_code: string | null;
    latitude: number | null;
    longitude: number | null;
}

export interface Category {
    id: number;
    name: string;
    type: RequestType;
}

export interface Organization extends Address {
    id: number;
    user_id: number;
    name: string;
    document: string | null;
    description: string | null;
    phone: string | null;
    email: string | null;
    website: string | null;
    status: OrganizationStatus;
    distance_km?: number | null;
    open_requests_count?: number;
    requests?: HelpRequest[];
}

/**
 * Perfil do voluntário. Campos opcionais só são enviados ao próprio
 * voluntário (controle de exposição de dados — ver VolunteerResource.php).
 */
export interface Volunteer extends Partial<Address> {
    id: number;
    name?: string;
    avatar_url?: string | null;
    bio: string | null;
    city: string | null;
    state: string | null;
    available: boolean;
    categories?: Category[];
    user_id?: number;
    birth_date?: string | null;
    phone?: string | null;
    location_consent?: boolean;
    location_consent_at?: string | null;
}

export interface User {
    id: number;
    name: string;
    email: string;
    type: UserType;
    phone: string | null;
    avatar_url: string | null;
    organization?: Organization | null;
    volunteer?: Volunteer | null;
    created_at: string | null;
}

export interface HelpRequest {
    id: number;
    organization_id: number;
    category_id: number | null;
    type: RequestType;
    title: string;
    description: string;
    quantity: number | null;
    priority: RequestPriority;
    status: RequestStatus;
    start_date: string | null;
    end_date: string | null;
    latitude: number | null;
    longitude: number | null;
    distance_km?: number | null;
    interested_count?: number;
    organization?: Organization;
    category?: Category | null;
    my_participation?: RequestVolunteer | null;
    created_at: string | null;
    updated_at: string | null;
}

export interface RequestVolunteer {
    id: number;
    request_id: number;
    volunteer_id: number;
    status: RequestVolunteerStatus;
    message: string | null;
    request?: HelpRequest;
    volunteer?: Volunteer;
    /** Só presente para a ONG, após aprovação. */
    contact?: { email: string; phone: string | null };
    created_at: string | null;
    updated_at: string | null;
}

export interface OrganizationDashboard {
    requests_by_status: Record<RequestStatus, number>;
    pending_interests: number;
    approved_volunteers: number;
    profile_complete: boolean;
}

export interface VolunteerDashboard {
    participations_by_status: Record<RequestVolunteerStatus, number>;
    open_requests: number;
    nearby_requests: number | null;
    profile_complete: boolean;
}
