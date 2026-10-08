<?php

namespace App\Http\Resources;

use App\Enums\RequestVolunteerStatus;
use App\Models\RequestVolunteer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Participação de um voluntário em uma solicitação.
 *
 * `contact` (e-mail/telefone do voluntário) só é incluído para a ONG dona da
 * solicitação e apenas depois que a participação foi aprovada.
 *
 * @mixin RequestVolunteer
 */
class RequestVolunteerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'request_id' => $this->request_id,
            'volunteer_id' => $this->volunteer_id,
            'status' => $this->status->value,
            'message' => $this->message,
            'request' => HelpRequestResource::make($this->whenLoaded('request')),
            'volunteer' => VolunteerResource::make($this->whenLoaded('volunteer')),
            'contact' => $this->when($this->contactVisibleTo($request), fn () => [
                'email' => $this->volunteer->user->email,
                'phone' => $this->volunteer->phone ?? $this->volunteer->user->phone,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    private function contactVisibleTo(Request $request): bool
    {
        $user = $request->user();

        return $user !== null
            && $this->relationLoaded('volunteer')
            && $this->volunteer->relationLoaded('user')
            && in_array($this->status, [RequestVolunteerStatus::Approved, RequestVolunteerStatus::Completed], true)
            && $this->request->isOwnedBy($user);
    }
}
