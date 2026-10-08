<?php

namespace App\Http\Controllers\Api;

use App\Actions\Requests\CreateHelpRequest;
use App\Actions\Requests\DeleteHelpRequest;
use App\Actions\Requests\UpdateHelpRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\HelpRequests\SearchHelpRequestsRequest;
use App\Http\Requests\HelpRequests\StoreHelpRequestRequest;
use App\Http\Requests\HelpRequests\UpdateHelpRequestRequest;
use App\Http\Resources\HelpRequestResource;
use App\Models\HelpRequest;
use App\Models\Organization;
use App\Models\User;
use App\Services\HelpRequestSearch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

/**
 * CRUD e pesquisa de solicitações. As regras de negócio ficam nas Actions;
 * aqui apenas orquestramos HTTP → autorização → action → resource.
 */
class HelpRequestController extends Controller
{
    public function index(SearchHelpRequestsRequest $request, HelpRequestSearch $search): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', HelpRequest::class);

        return HelpRequestResource::collection($search->search($this->user($request), $request->validated()));
    }

    public function store(StoreHelpRequestRequest $request, CreateHelpRequest $createRequest): JsonResponse
    {
        Gate::authorize('create', HelpRequest::class);

        /** @var Organization $organization */
        $organization = $this->user($request)->organization;

        return HelpRequestResource::make($createRequest->handle($organization, $request->validated()))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, HelpRequest $helpRequest): HelpRequestResource
    {
        Gate::authorize('view', $helpRequest);

        $helpRequest->load(['organization', 'category'])->loadCount([
            'participations as interested_count' => fn ($q) => $q->whereIn('status', ['interested', 'approved']),
        ]);

        // Para voluntários, inclui a situação da própria participação (se houver).
        if ($volunteer = $this->user($request)->volunteer) {
            $helpRequest->setRelation(
                'myParticipation',
                $helpRequest->participations()->where('volunteer_id', $volunteer->id)->first(),
            );
        }

        return HelpRequestResource::make($helpRequest);
    }

    public function update(UpdateHelpRequestRequest $request, HelpRequest $helpRequest, UpdateHelpRequest $updateRequest): HelpRequestResource
    {
        Gate::authorize('update', $helpRequest);

        return HelpRequestResource::make($updateRequest->handle($helpRequest, $request->validated()));
    }

    public function destroy(HelpRequest $helpRequest, DeleteHelpRequest $deleteRequest): JsonResponse
    {
        Gate::authorize('delete', $helpRequest);

        $deleted = $deleteRequest->handle($helpRequest);

        return response()->json([
            'deleted' => $deleted,
            'message' => $deleted
                ? 'Solicitação excluída.'
                : 'A solicitação possui voluntários vinculados e foi cancelada para preservar o histórico.',
            'data' => $deleted ? null : HelpRequestResource::make($helpRequest->refresh()),
        ]);
    }

    private function user(Request $request): User
    {
        /** @var User */
        return $request->user();
    }
}
