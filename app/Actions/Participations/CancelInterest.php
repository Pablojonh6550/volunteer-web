<?php

namespace App\Actions\Participations;

use App\Enums\RequestVolunteerStatus;
use App\Exceptions\BusinessRuleException;
use App\Models\HelpRequest;
use App\Models\RequestVolunteer;
use App\Models\Volunteer;

/**
 * Voluntário desiste de participar de uma solicitação.
 */
class CancelInterest
{
    public function handle(HelpRequest $request, Volunteer $volunteer): RequestVolunteer
    {
        /** @var RequestVolunteer|null $participation */
        $participation = $request->participations()->where('volunteer_id', $volunteer->id)->first();

        if ($participation === null || ! $participation->status->cancellableByVolunteer()) {
            throw new BusinessRuleException('Não há interesse ativo para cancelar nesta solicitação.');
        }

        $participation->update(['status' => RequestVolunteerStatus::Cancelled]);

        return $participation;
    }
}
