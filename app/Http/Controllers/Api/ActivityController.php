<?php

namespace App\Http\Controllers\Api;

use App\Enums\RequestVolunteerStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\RequestVolunteerResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

/**
 * "Minhas atividades": participações e histórico do voluntário autenticado.
 */
class ActivityController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::enum(RequestVolunteerStatus::class)],
        ]);

        /** @var User $user */
        $user = $request->user();

        $participations = $user->volunteer?->participations()
            ->with(['request.organization', 'request.category'])
            ->when($validated['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->latest('updated_at')
            ->paginate(20);

        return RequestVolunteerResource::collection($participations ?? collect());
    }
}
