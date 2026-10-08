<?php

namespace App\Actions\Requests;

use App\Enums\RequestStatus;
use App\Models\HelpRequest;

/**
 * "Cancelar/excluir" uma solicitação (DELETE /api/requests/{id}).
 *
 * - Sem voluntários vinculados: a solicitação é excluída definitivamente.
 * - Com voluntários vinculados: é apenas cancelada, preservando o histórico
 *   das atividades dos voluntários.
 *
 * @return bool true quando excluída, false quando cancelada.
 */
class DeleteHelpRequest
{
    public function __construct(private UpdateHelpRequest $updateHelpRequest) {}

    public function handle(HelpRequest $request): bool
    {
        if (! $request->participations()->exists()) {
            $request->delete();

            return true;
        }

        if ($request->status !== RequestStatus::Cancelled) {
            $this->updateHelpRequest->handle($request, ['status' => RequestStatus::Cancelled->value]);
        }

        return false;
    }
}
