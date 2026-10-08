<?php

namespace App\Policies;

use App\Enums\OrganizationStatus;
use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy
{
    /** Qualquer usuário autenticado pode listar ONGs ativas. */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /** ONGs inativas só são visíveis para o próprio dono. */
    public function view(User $user, Organization $organization): bool
    {
        return $organization->status === OrganizationStatus::Active
            || $organization->user_id === $user->id;
    }

    /** Somente o usuário dono da ONG pode alterar seus dados. */
    public function update(User $user, Organization $organization): bool
    {
        return $organization->user_id === $user->id;
    }
}
