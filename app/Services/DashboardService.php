<?php

namespace App\Services;

use App\Enums\OrganizationStatus;
use App\Enums\RequestStatus;
use App\Enums\RequestVolunteerStatus;
use App\Models\HelpRequest;
use App\Models\Organization;
use App\Models\RequestVolunteer;
use App\Models\User;
use App\Models\Volunteer;

/**
 * Indicadores exibidos nos dashboards de ONG e voluntário (GET /api/dashboard).
 */
class DashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function forUser(User $user): array
    {
        return match (true) {
            $user->organization !== null => $this->forOrganization($user->organization),
            $user->volunteer !== null => $this->forVolunteer($user->volunteer),
            default => [],
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function forOrganization(Organization $organization): array
    {
        $byStatus = $organization->requests()
            ->toBase()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $pendingInterests = RequestVolunteer::query()
            ->where('status', RequestVolunteerStatus::Interested)
            ->whereHas('request', fn ($q) => $q->where('organization_id', $organization->id))
            ->count();

        return [
            'requests_by_status' => collect(RequestStatus::cases())
                ->mapWithKeys(fn (RequestStatus $s) => [$s->value => (int) ($byStatus[$s->value] ?? 0)]),
            'pending_interests' => $pendingInterests,
            'approved_volunteers' => RequestVolunteer::query()
                ->whereIn('status', [RequestVolunteerStatus::Approved, RequestVolunteerStatus::Completed])
                ->whereHas('request', fn ($q) => $q->where('organization_id', $organization->id))
                ->count(),
            'profile_complete' => $organization->latitude !== null && $organization->description !== null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function forVolunteer(Volunteer $volunteer): array
    {
        $byStatus = $volunteer->participations()
            ->toBase()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'participations_by_status' => collect(RequestVolunteerStatus::cases())
                ->mapWithKeys(fn (RequestVolunteerStatus $s) => [$s->value => (int) ($byStatus[$s->value] ?? 0)]),
            'open_requests' => HelpRequest::query()
                ->whereIn('status', RequestStatus::publicStatuses())
                ->whereHas('organization', fn ($q) => $q->where('status', OrganizationStatus::Active))
                ->count(),
            'nearby_requests' => $volunteer->latitude !== null && $volunteer->longitude !== null
                ? HelpRequest::query()
                    ->whereIn('status', RequestStatus::publicStatuses())
                    ->nearby($volunteer->latitude, $volunteer->longitude, 10)
                    ->count()
                : null,
            'profile_complete' => $volunteer->hasLocationConsent() && $volunteer->categories()->exists(),
        ];
    }
}
