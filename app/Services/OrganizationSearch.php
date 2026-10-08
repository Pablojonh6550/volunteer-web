<?php

namespace App\Services;

use App\Models\Organization;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * Listagem de ONGs ativas (GET /api/organizations), usada no mapa de ONGs próximas.
 *
 * Filtros: q (nome), city, lat/lng (+ radius em km). Com lat/lng o resultado
 * inclui `distance_km` e é ordenado da mais próxima para a mais distante.
 */
class OrganizationSearch
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Organization>
     */
    public function search(array $filters): LengthAwarePaginator
    {
        $query = Organization::query()
            ->active()
            ->withCount(['requests as open_requests_count' => fn (Builder $q) => $q->whereIn('status', ['open', 'in_progress'])])
            ->when($filters['q'] ?? null, fn (Builder $q, string $term) => $q->where('name', 'like', '%'.$term.'%'))
            ->when($filters['city'] ?? null, fn (Builder $q, string $city) => $q->where('city', $city));

        if (isset($filters['lat'], $filters['lng'])) {
            $lat = (float) $filters['lat'];
            $lng = (float) $filters['lng'];

            $query->withDistanceFrom($lat, $lng)
                ->when(isset($filters['radius']), fn (Builder $q) => $q->nearby($lat, $lng, (float) $filters['radius']))
                ->orderByRaw('distance_km IS NULL')
                ->orderBy('distance_km');
        } else {
            $query->orderBy('name');
        }

        $perPage = min((int) ($filters['per_page'] ?? 50), 100);

        return $query->paginate($perPage)->withQueryString();
    }
}
