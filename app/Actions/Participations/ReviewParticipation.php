<?php

namespace App\Actions\Participations;

use App\Enums\RequestStatus;
use App\Enums\RequestVolunteerStatus;
use App\Exceptions\BusinessRuleException;
use App\Models\RequestVolunteer;
use App\Notifications\ParticipationStatusChanged;

/**
 * ONG analisa um voluntário: aprova, recusa ou marca a participação como concluída.
 *
 * As transições permitidas estão em RequestVolunteerStatus::organizationTransitions().
 * Ao aprovar o primeiro voluntário, uma solicitação "aberta" passa para "em andamento".
 */
class ReviewParticipation
{
    public function handle(RequestVolunteer $participation, RequestVolunteerStatus $status): RequestVolunteer
    {
        $request = $participation->request;

        if ($request->status->isFinal()) {
            throw new BusinessRuleException('A solicitação já foi finalizada.');
        }

        if (! in_array($status, $participation->status->organizationTransitions(), true)) {
            throw new BusinessRuleException(sprintf(
                'Não é possível alterar a participação de "%s" para "%s".',
                $participation->status->label(),
                $status->label(),
            ));
        }

        $participation->update(['status' => $status]);

        if ($status === RequestVolunteerStatus::Approved && $request->status === RequestStatus::Open) {
            $request->update(['status' => RequestStatus::InProgress]);
        }

        $participation->volunteer->user->notify(new ParticipationStatusChanged($participation));

        return $participation->load(['request', 'volunteer.user', 'volunteer.categories']);
    }
}
