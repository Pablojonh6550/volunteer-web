<?php

namespace App\Http\Resources;

use App\Models\Volunteer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Perfil do voluntário com controle de exposição de dados pessoais (LGPD):
 *
 *  - O próprio voluntário vê todos os seus dados.
 *  - Terceiros (ONGs) veem apenas o perfil público: nome, avatar, bio,
 *    cidade/UF, disponibilidade e áreas de interesse. Endereço, data de
 *    nascimento, telefone e coordenadas nunca são expostos a terceiros.
 *    O contato só é liberado pela ONG após aprovação (RequestVolunteerResource).
 *
 * @mixin Volunteer
 */
class VolunteerResource extends JsonResource
{
    /**
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isOwner = $request->user()?->id === $this->user_id;

        return [
            'id' => $this->id,
            'name' => $this->whenLoaded('user', fn () => $this->user->name),
            'avatar_url' => $this->whenLoaded('user', fn () => $this->user->avatarUrl()),
            'bio' => $this->bio,
            'city' => $this->city,
            'state' => $this->state,
            'available' => $this->available,
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            $this->mergeWhen($isOwner, fn () => [
                'user_id' => $this->user_id,
                'birth_date' => $this->birth_date?->toDateString(),
                'phone' => $this->phone,
                'address' => $this->address,
                'number' => $this->number,
                'neighborhood' => $this->neighborhood,
                'zip_code' => $this->zip_code,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'location_consent' => $this->hasLocationConsent(),
                'location_consent_at' => $this->location_consent_at?->toIso8601String(),
            ]),
        ];
    }
}
