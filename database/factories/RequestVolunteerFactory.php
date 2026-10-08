<?php

namespace Database\Factories;

use App\Enums\RequestVolunteerStatus;
use App\Models\HelpRequest;
use App\Models\RequestVolunteer;
use App\Models\Volunteer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RequestVolunteer>
 */
class RequestVolunteerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'request_id' => HelpRequest::factory(),
            'volunteer_id' => Volunteer::factory(),
            'status' => RequestVolunteerStatus::Interested,
            'message' => fake()->sentence(),
        ];
    }

    public function status(RequestVolunteerStatus $status): static
    {
        return $this->state(['status' => $status]);
    }
}
