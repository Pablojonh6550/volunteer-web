<?php

namespace App\Policies;

use App\Models\RequestVolunteer;
use App\Models\User;

class RequestVolunteerPolicy
{
    /** Apenas a ONG dona da solicitação aprova, recusa ou conclui participações. */
    public function update(User $user, RequestVolunteer $participation): bool
    {
        return $participation->request->isOwnedBy($user);
    }
}
