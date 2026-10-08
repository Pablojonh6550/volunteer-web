<?php

namespace App\Actions\Participations;

use App\Enums\RequestVolunteerStatus;
use App\Exceptions\BusinessRuleException;
use App\Models\HelpRequest;
use App\Models\RequestVolunteer;
use App\Models\Volunteer;
use App\Notifications\VolunteerInterestReceived;

/**
 * Voluntário demonstra interesse em uma solicitação (início do fluxo de interesse).
 *
 * - Só é possível em solicitações abertas ou em andamento.
 * - Um voluntário tem no máximo um vínculo por solicitação; se ele havia
 *   cancelado o interesse anteriormente, o vínculo é reativado.
 * - A ONG é notificada de forma assíncrona (fila).
 */
class ExpressInterest
{
    public function handle(HelpRequest $request, Volunteer $volunteer, ?string $message = null): RequestVolunteer
    {
        if (! $request->status->acceptsInterest()) {
            throw new BusinessRuleException('Esta solicitação não está aceitando novos interessados.');
        }

        /** @var RequestVolunteer|null $participation */
        $participation = $request->participations()->where('volunteer_id', $volunteer->id)->first();

        if ($participation !== null && $participation->status !== RequestVolunteerStatus::Cancelled) {
            throw new BusinessRuleException('Você já demonstrou interesse nesta solicitação.');
        }

        $participation ??= new RequestVolunteer([
            'request_id' => $request->id,
            'volunteer_id' => $volunteer->id,
        ]);

        $participation->fill([
            'status' => RequestVolunteerStatus::Interested,
            'message' => $message,
        ])->save();

        $request->organization->user->notify(new VolunteerInterestReceived($participation));

        return $participation->load(['request.organization', 'volunteer.user']);
    }
}
