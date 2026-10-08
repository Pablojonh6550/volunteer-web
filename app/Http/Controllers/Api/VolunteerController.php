<?php

namespace App\Http\Controllers\Api;

use App\Actions\Volunteers\UpdateVolunteerProfile;
use App\Http\Controllers\Controller;
use App\Http\Requests\Volunteers\UpdateVolunteerRequest;
use App\Http\Resources\VolunteerResource;
use App\Models\Volunteer;
use Illuminate\Support\Facades\Gate;

class VolunteerController extends Controller
{
    public function show(Volunteer $volunteer): VolunteerResource
    {
        Gate::authorize('view', $volunteer);

        return VolunteerResource::make($volunteer->load(['user', 'categories']));
    }

    public function update(UpdateVolunteerRequest $request, Volunteer $volunteer, UpdateVolunteerProfile $updateProfile): VolunteerResource
    {
        Gate::authorize('update', $volunteer);

        return VolunteerResource::make($updateProfile->handle($volunteer, $request->validated()));
    }
}
