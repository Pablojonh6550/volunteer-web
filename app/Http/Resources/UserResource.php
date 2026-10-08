<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Usuário autenticado (GET /api/me). Nunca expõe senha nem tokens.
 *
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'type' => $this->type->value,
            'phone' => $this->phone,
            'avatar_url' => $this->avatarUrl(),
            'organization' => OrganizationResource::make($this->whenLoaded('organization')),
            'volunteer' => VolunteerResource::make($this->whenLoaded('volunteer')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
