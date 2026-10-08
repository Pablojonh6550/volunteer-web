<?php

namespace App\Http\Resources;

use App\Models\HelpRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin HelpRequest
 */
class HelpRequestResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'category_id' => $this->category_id,
            'type' => $this->type->value,
            'title' => $this->title,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'priority' => $this->priority->value,
            'status' => $this->status->value,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'distance_km' => $this->whenHas('distance_km', fn ($d) => $d === null ? null : round((float) $d, 2)),
            'interested_count' => $this->whenCounted('interested_count'),
            'organization' => OrganizationResource::make($this->whenLoaded('organization')),
            'category' => CategoryResource::make($this->whenLoaded('category')),
            // Participação do voluntário autenticado (preenchida em HelpRequestController@show).
            'my_participation' => $this->when(
                $this->relationLoaded('myParticipation'),
                fn () => RequestVolunteerResource::make($this->getRelation('myParticipation')),
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
