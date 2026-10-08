<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\HelpRequest;
use App\Models\Organization;
use App\Models\RequestVolunteer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_can_update_its_own_profile(): void
    {
        $user = User::factory()->organization()->create();
        $organization = $user->organization;

        $this->actingAs($user)->putJson("/api/organizations/{$organization?->id}", [
            'name' => 'ONG Atualizada',
            'document' => '12.345.678/0001-90',
            'zip_code' => '01310-100',
            'state' => 'sp',
            'latitude' => -23.56,
            'longitude' => -46.65,
        ])->assertOk()
            ->assertJsonPath('data.name', 'ONG Atualizada')
            ->assertJsonPath('data.document', '12345678000190')
            ->assertJsonPath('data.zip_code', '01310100')
            ->assertJsonPath('data.state', 'SP');
    }

    public function test_organization_cannot_update_another_organization(): void
    {
        $user = User::factory()->organization()->create();
        $other = Organization::factory()->create();

        $this->actingAs($user)->putJson("/api/organizations/{$other->id}", ['name' => 'Hack'])
            ->assertForbidden();
    }

    public function test_organizations_can_be_filtered_by_distance(): void
    {
        $user = User::factory()->volunteer()->create();
        // Praça da Sé (SP) como referência.
        $near = Organization::factory()->at(-23.5503, -46.6340)->create(['name' => 'Perto']);
        $far = Organization::factory()->at(-22.9068, -43.1729)->create(['name' => 'Rio']); // ~360 km

        $response = $this->actingAs($user)->getJson('/api/organizations?lat=-23.5505&lng=-46.6333&radius=10')
            ->assertOk();

        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($near->id));
        $this->assertFalse($ids->contains($far->id));
        $this->assertLessThan(1, $response->json('data.0.distance_km'));
    }

    public function test_volunteer_updates_profile_interests_and_location_with_consent(): void
    {
        $user = User::factory()->create(['type' => 'volunteer']);
        $volunteer = $user->volunteer()->create();
        $categories = Category::factory()->count(2)->create();

        $this->actingAs($user)->putJson("/api/volunteers/{$volunteer->id}", [
            'bio' => 'Gosto de ajudar',
            'category_ids' => $categories->pluck('id')->all(),
            'location_consent' => true,
            'latitude' => -23.55,
            'longitude' => -46.63,
        ])->assertOk()
            ->assertJsonPath('data.location_consent', true)
            ->assertJsonPath('data.latitude', -23.55)
            ->assertJsonCount(2, 'data.categories');
    }

    public function test_volunteer_location_is_discarded_without_consent(): void
    {
        $user = User::factory()->create(['type' => 'volunteer']);
        $volunteer = $user->volunteer()->create();

        $this->actingAs($user)->putJson("/api/volunteers/{$volunteer->id}", [
            'latitude' => -23.55,
            'longitude' => -46.63,
        ])->assertOk()->assertJsonPath('data.latitude', null);
    }

    public function test_revoking_consent_erases_coordinates(): void
    {
        $user = User::factory()->volunteer()->create();
        $volunteer = $user->volunteer;

        $this->actingAs($user)->putJson("/api/volunteers/{$volunteer?->id}", ['location_consent' => false])
            ->assertOk()
            ->assertJsonPath('data.location_consent', false)
            ->assertJsonPath('data.latitude', null);
    }

    public function test_volunteer_profile_is_private_to_unrelated_organizations(): void
    {
        $volunteer = User::factory()->volunteer()->create()->volunteer;
        $organizationUser = User::factory()->organization()->create();

        $this->actingAs($organizationUser)->getJson("/api/volunteers/{$volunteer?->id}")->assertForbidden();
    }

    public function test_related_organization_sees_only_public_volunteer_data(): void
    {
        $volunteer = User::factory()->volunteer()->create()->volunteer;
        $organizationUser = User::factory()->organization()->create();
        $request = HelpRequest::factory()->for($organizationUser->organization)->create();
        RequestVolunteer::factory()->create(['request_id' => $request->id, 'volunteer_id' => $volunteer?->id]);

        $this->actingAs($organizationUser)->getJson("/api/volunteers/{$volunteer?->id}")
            ->assertOk()
            ->assertJsonStructure(['data' => ['id', 'name', 'bio', 'city']])
            ->assertJsonMissingPath('data.birth_date')
            ->assertJsonMissingPath('data.address')
            ->assertJsonMissingPath('data.latitude')
            ->assertJsonMissingPath('data.phone');
    }
}
