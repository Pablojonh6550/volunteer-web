<?php

namespace App\Http\Controllers\Api;

use App\Actions\Participations\CancelInterest;
use App\Actions\Participations\ExpressInterest;
use App\Http\Controllers\Controller;
use App\Http\Requests\Participations\ExpressInterestRequest;
use App\Http\Resources\RequestVolunteerResource;
use App\Models\HelpRequest;
use App\Models\User;
use App\Models\Volunteer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Manifestação de interesse do voluntário em uma solicitação.
 */
class InterestController extends Controller
{
    public function store(ExpressInterestRequest $request, HelpRequest $helpRequest, ExpressInterest $expressInterest): JsonResponse
    {
        Gate::authorize('view', $helpRequest);
        Gate::authorize('expressInterest', $helpRequest);

        $participation = $expressInterest->handle($helpRequest, $this->volunteer($request), $request->validated('message'));

        return RequestVolunteerResource::make($participation)->response()->setStatusCode(201);
    }

    public function destroy(Request $request, HelpRequest $helpRequest, CancelInterest $cancelInterest): RequestVolunteerResource
    {
        Gate::authorize('expressInterest', $helpRequest);

        return RequestVolunteerResource::make($cancelInterest->handle($helpRequest, $this->volunteer($request)));
    }

    private function volunteer(Request $request): Volunteer
    {
        /** @var User $user */
        $user = $request->user();

        /** @var Volunteer */
        return $user->volunteer;
    }
}
