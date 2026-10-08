<?php

namespace App\Policies;

use App\Enums\OrganizationStatus;
use App\Models\HelpRequest;
use App\Models\User;

class HelpRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Solicitações abertas/em andamento de ONGs ativas são públicas para
     * usuários autenticados. As demais só são visíveis para a ONG dona e
     * para voluntários que participaram delas (histórico).
     */
    public function view(User $user, HelpRequest $request): bool
    {
        if ($request->isOwnedBy($user)) {
            return true;
        }

        if ($request->status->acceptsInterest() && $request->organization->status === OrganizationStatus::Active) {
            return true;
        }

        $volunteerId = $user->volunteer?->id;

        return $volunteerId !== null && $request->participations()->where('volunteer_id', $volunteerId)->exists();
    }

    /** Apenas ONGs publicam solicitações. */
    public function create(User $user): bool
    {
        return $user->isOrganization() && $user->organization !== null;
    }

    public function update(User $user, HelpRequest $request): bool
    {
        return $request->isOwnedBy($user);
    }

    public function delete(User $user, HelpRequest $request): bool
    {
        return $request->isOwnedBy($user);
    }

    /** Apenas voluntários demonstram interesse. */
    public function expressInterest(User $user, HelpRequest $request): bool
    {
        return $user->isVolunteer() && $user->volunteer !== null;
    }

    /** Apenas a ONG dona consulta os interessados. */
    public function viewVolunteers(User $user, HelpRequest $request): bool
    {
        return $request->isOwnedBy($user);
    }
}
