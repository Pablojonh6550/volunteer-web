<?php

namespace App\Actions\Requests;

use App\Enums\RequestStatus;
use App\Models\HelpRequest;
use App\Models\Organization;

/**
 * Publica uma nova solicitação para a ONG.
 *
 * Quando o local da atividade não é informado, a solicitação herda a
 * latitude/longitude da ONG, para aparecer na busca por proximidade.
 */
class CreateHelpRequest
{
    /**
     * @param  array<string, mixed>  $data  Dados validados por StoreHelpRequestRequest.
     */
    public function handle(Organization $organization, array $data): HelpRequest
    {
        $data['latitude'] ??= $organization->latitude;
        $data['longitude'] ??= $organization->longitude;

        /** @var HelpRequest $request */
        $request = $organization->requests()->make($data);
        $request->status = RequestStatus::Open;
        $request->save();

        return $request->load(['organization', 'category']);
    }
}
