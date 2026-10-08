/**
 * Formatos genéricos de resposta da API Laravel.
 */

/** Envelope padrão dos API Resources: `{ data: T }`. */
export interface ApiResource<T> {
    data: T;
}

/** Resposta paginada (LengthAwarePaginator + ResourceCollection). */
export interface Paginated<T> {
    data: T[];
    links: {
        first: string | null;
        last: string | null;
        prev: string | null;
        next: string | null;
    };
    meta: {
        current_page: number;
        from: number | null;
        last_page: number;
        per_page: number;
        to: number | null;
        total: number;
    };
}

/** Corpo de erro 422 (validação ou regra de negócio). */
export interface ValidationErrorBody {
    message: string;
    errors?: Record<string, string[]>;
}

/** Erro normalizado pela camada de serviços (services/http.ts). */
export interface ApiError {
    status: number;
    message: string;
    errors: Record<string, string[]>;
}

export interface RequestFilters {
    q?: string;
    type?: string;
    category_id?: number | string;
    priority?: string;
    status?: string;
    organization_id?: number;
    mine?: boolean;
    lat?: number;
    lng?: number;
    radius?: number;
    sort?: 'recent' | 'distance' | 'priority';
    page?: number;
    per_page?: number;
}

export interface OrganizationFilters {
    q?: string;
    city?: string;
    lat?: number;
    lng?: number;
    radius?: number;
    per_page?: number;
}
