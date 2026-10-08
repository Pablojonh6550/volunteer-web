<?php

namespace App\Actions\Requests;

use App\Enums\RequestStatus;
use App\Enums\RequestVolunteerStatus;
use App\Exceptions\BusinessRuleException;
use App\Models\HelpRequest;
use Illuminate\Support\Facades\DB;

/**
 * Atualiza dados e/ou status de uma solicitação, aplicando as regras do ciclo de vida.
 *
 * - Solicitações concluídas ou canceladas não podem ser editadas.
 * - A troca de status respeita RequestStatus::allowedTransitions().
 * - Ao concluir: voluntários aprovados passam a "completed" e interessados
 *   pendentes são recusados.
 * - Ao cancelar: todas as participações ativas passam a "cancelled".
 */
class UpdateHelpRequest
{
    /**
     * @param  array<string, mixed>  $data  Dados validados por UpdateHelpRequestRequest.
     */
    public function handle(HelpRequest $request, array $data): HelpRequest
    {
        if ($request->status->isFinal()) {
            throw new BusinessRuleException('Solicitações concluídas ou canceladas não podem ser alteradas.');
        }

        $newStatus = isset($data['status']) ? RequestStatus::from($data['status']) : $request->status;

        if (! $request->status->canTransitionTo($newStatus)) {
            throw new BusinessRuleException(sprintf(
                'Não é possível alterar a solicitação de "%s" para "%s".',
                $request->status->label(),
                $newStatus->label(),
            ));
        }

        return DB::transaction(function () use ($request, $data, $newStatus): HelpRequest {
            $previous = $request->status;
            $request->update($data);

            if ($previous !== $newStatus) {
                $this->syncParticipations($request, $newStatus);
            }

            return $request->refresh()->load(['organization', 'category']);
        });
    }

    private function syncParticipations(HelpRequest $request, RequestStatus $status): void
    {
        match ($status) {
            RequestStatus::Completed => $this->complete($request),
            RequestStatus::Cancelled => $request->participations()
                ->whereIn('status', [RequestVolunteerStatus::Interested, RequestVolunteerStatus::Approved])
                ->update(['status' => RequestVolunteerStatus::Cancelled]),
            default => null,
        };
    }

    private function complete(HelpRequest $request): void
    {
        $request->participations()
            ->where('status', RequestVolunteerStatus::Approved)
            ->update(['status' => RequestVolunteerStatus::Completed]);

        $request->participations()
            ->where('status', RequestVolunteerStatus::Interested)
            ->update(['status' => RequestVolunteerStatus::Rejected]);
    }
}
