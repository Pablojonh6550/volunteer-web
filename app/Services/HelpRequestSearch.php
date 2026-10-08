<?php

namespace App\Services;

use App\Enums\OrganizationStatus;
use App\Enums\RequestPriority;
use App\Enums\RequestStatus;
use App\Models\HelpRequest;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * Pesquisa de solicitações (GET /api/requests).
 *
 * Filtros suportados (todos opcionais):
 *  - q               texto em título/descrição
 *  - type            RequestType
 *  - category_id     categoria
 *  - priority        RequestPriority
 *  - status          RequestStatus (ver regra de visibilidade abaixo)
 *  - organization_id solicitações de uma ONG
 *  - mine            (ONG) apenas as próprias solicitações, em qualquer status
 *  - lat, lng        ponto de referência para calcular `distance_km`
 *  - radius          raio em km (exige lat/lng)
 *  - sort            recent | distance | priority
 *
 * Visibilidade: voluntários (e ONGs olhando solicitações de terceiros) só enxergam
 * solicitações abertas ou em andamento de ONGs ativas. A ONG vê todas as suas.
 */
class HelpRequestSearch
{
    public const DEFAULT_PER_PAGE = 12;

    public const MAX_PER_PAGE = 50;

    /**
     * @param  array<string, mixed>  $filters  Dados validados por SearchHelpRequestsRequest.
     * @return LengthAwarePaginator<int, HelpRequest>
     */
    public function search(User $user, array $filters): LengthAwarePaginator
    {
        $query = HelpRequest::query()
            ->with(['organization', 'category'])
            ->withCount(['participations as interested_count' => fn (Builder $q) => $q->whereIn('status', ['interested', 'approved'])]);

        $this->applyVisibility($query, $user, $filters);
        $this->applyFilters($query, $filters);
        $this->applyLocation($query, $filters);
        $this->applySorting($query, $filters);

        $perPage = min((int) ($filters['per_page'] ?? self::DEFAULT_PER_PAGE), self::MAX_PER_PAGE);

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * @param  Builder<HelpRequest>  $query
     * @param  array<string, mixed>  $filters
     */
    private function applyVisibility(Builder $query, User $user, array $filters): void
    {
        $ownOrganizationId = $user->organization?->id;

        if (! empty($filters['mine']) && $ownOrganizationId !== null) {
            $query->where('organization_id', $ownOrganizationId);

            if (! empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            return;
        }

        $query->whereHas('organization', fn (Builder $q) => $q->where('status', OrganizationStatus::Active));

        $statuses = array_map(fn (RequestStatus $s) => $s->value, RequestStatus::publicStatuses());
        $requested = $filters['status'] ?? null;

        $query->whereIn('status', $requested && in_array($requested, $statuses, true) ? [$requested] : $statuses);
    }

    /**
     * @param  Builder<HelpRequest>  $query
     * @param  array<string, mixed>  $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        $query
            ->when($filters['q'] ?? null, function (Builder $q, string $term) {
                // Bindings parametrizados: o termo nunca é concatenado ao SQL (proteção contra SQL Injection).
                $like = '%'.$term.'%';
                $q->where(fn (Builder $w) => $w->where('title', 'like', $like)->orWhere('description', 'like', $like));
            })
            ->when($filters['type'] ?? null, fn (Builder $q, string $type) => $q->where('type', $type))
            ->when($filters['category_id'] ?? null, fn (Builder $q, int|string $id) => $q->where('category_id', $id))
            ->when($filters['priority'] ?? null, fn (Builder $q, string $priority) => $q->where('priority', $priority))
            ->when($filters['organization_id'] ?? null, fn (Builder $q, int|string $id) => $q->where('organization_id', $id));
    }

    /**
     * @param  Builder<HelpRequest>  $query
     * @param  array<string, mixed>  $filters
     */
    private function applyLocation(Builder $query, array $filters): void
    {
        if (! isset($filters['lat'], $filters['lng'])) {
            return;
        }

        $lat = (float) $filters['lat'];
        $lng = (float) $filters['lng'];

        $query->withDistanceFrom($lat, $lng);

        if (isset($filters['radius'])) {
            $query->nearby($lat, $lng, (float) $filters['radius']);
        }
    }

    /**
     * @param  Builder<HelpRequest>  $query
     * @param  array<string, mixed>  $filters
     */
    private function applySorting(Builder $query, array $filters): void
    {
        $sort = $filters['sort'] ?? 'recent';

        if ($sort === 'distance' && isset($filters['lat'], $filters['lng'])) {
            $query->orderByRaw('distance_km IS NULL')->orderBy('distance_km');

            return;
        }

        if ($sort === 'priority') {
            // SQL montado apenas a partir do enum (sem entrada do usuário).
            /** @var literal-string $cases */
            $cases = collect(RequestPriority::cases())
                ->map(fn (RequestPriority $p) => "WHEN '{$p->value}' THEN {$p->weight()}")
                ->implode(' ');
            $query->orderByRaw("CASE priority {$cases} END DESC");
        }

        $query->latest()->orderByDesc('id');
    }
}
