<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organizations\SearchOrganizationsRequest;
use App\Http\Requests\Organizations\UpdateOrganizationRequest;
use App\Http\Resources\OrganizationResource;
use App\Models\Organization;
use App\Services\OrganizationSearch;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class OrganizationController extends Controller
{
    public function index(SearchOrganizationsRequest $request, OrganizationSearch $search): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Organization::class);

        return OrganizationResource::collection($search->search($request->validated()));
    }

    public function show(Organization $organization): OrganizationResource
    {
        Gate::authorize('view', $organization);

        $organization->load(['requests' => fn ($q) => $q->whereIn('status', ['open', 'in_progress'])->with('category')->latest()]);

        return OrganizationResource::make($organization);
    }

    public function update(UpdateOrganizationRequest $request, Organization $organization): OrganizationResource
    {
        Gate::authorize('update', $organization);

        $organization->update($request->validated());

        return OrganizationResource::make($organization);
    }
}
