<?php

namespace App\Http\Resources;

use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Dados institucionais da ONG. São dados públicos de pessoa jurídica,
 * portanto visíveis a qualquer usuário autenticado.
 *
 * @mixin Organization
 */
class OrganizationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'document' => $this->document,
            'description' => $this->description,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
            'address' => $this->address,
            'number' => $this->number,
            'neighborhood' => $this->neighborhood,
            'city' => $this->city,
            'state' => $this->state,
            'zip_code' => $this->zip_code,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'status' => $this->status->value,
            'distance_km' => $this->whenHas('distance_km', fn ($d) => $d === null ? null : round((float) $d, 2)),
            'open_requests_count' => $this->whenCounted('open_requests_count'),
            'requests' => HelpRequestResource::collection($this->whenLoaded('requests')),
        ];
    }
}
