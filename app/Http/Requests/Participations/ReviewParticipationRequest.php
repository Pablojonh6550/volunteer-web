<?php

namespace App\Http\Requests\Participations;

use App\Enums\RequestVolunteerStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewParticipationRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(RequestVolunteerStatus::class)->only([
                RequestVolunteerStatus::Approved,
                RequestVolunteerStatus::Rejected,
                RequestVolunteerStatus::Completed,
            ])],
        ];
    }
}
