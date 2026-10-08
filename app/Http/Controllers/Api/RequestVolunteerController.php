<?php

namespace App\Http\Controllers\Api;

use App\Actions\Participations\ReviewParticipation;
use App\Enums\RequestVolunteerStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Participations\ReviewParticipationRequest;
use App\Http\Resources\RequestVolunteerResource;
use App\Models\HelpRequest;
use App\Models\RequestVolunteer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

/**
 * Lado da ONG no fluxo de interesse: listar interessados e aprovar/recusar/concluir.
 */
class RequestVolunteerController extends Controller
{
    public function index(Request $request, HelpRequest $helpRequest): AnonymousResourceCollection
    {
        Gate::authorize('viewVolunteers', $helpRequest);

        $validated = $request->validate([
            'status' => ['nullable', Rule::enum(RequestVolunteerStatus::class)],
        ]);

        $participations = $helpRequest->participations()
            ->with(['volunteer.user', 'volunteer.categories', 'request'])
            ->when($validated['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->oldest()
            ->get();

        return RequestVolunteerResource::collection($participations);
    }

    public function update(ReviewParticipationRequest $request, RequestVolunteer $requestVolunteer, ReviewParticipation $review): RequestVolunteerResource
    {
        Gate::authorize('update', $requestVolunteer);

        $status = RequestVolunteerStatus::from($request->validated('status'));

        return RequestVolunteerResource::make($review->handle($requestVolunteer, $status));
    }
}
