<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Volunteer;

class VolunteerPolicy
{
    /**
     * O perfil é visível para o próprio voluntário e para ONGs em cujas
     * solicitações ele demonstrou interesse. Não existe listagem pública
     * de voluntários (minimização de dados — LGPD).
     */
    public function view(User $user, Volunteer $volunteer): bool
    {
        if ($volunteer->user_id === $user->id) {
            return true;
        }

        $organizationId = $user->organization?->id;

        return $organizationId !== null && $volunteer->participations()
            ->whereHas('request', fn ($q) => $q->where('organization_id', $organizationId))
            ->exists();
    }

    public function update(User $user, Volunteer $volunteer): bool
    {
        return $volunteer->user_id === $user->id;
    }
}
