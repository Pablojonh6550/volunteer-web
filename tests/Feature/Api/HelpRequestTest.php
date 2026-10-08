<?php

namespace Tests\Feature\Api;

use App\Enums\RequestStatus;
use App\Enums\RequestVolunteerStatus;
use App\Models\Category;
use App\Models\HelpRequest;
use App\Models\Organization;
use App\Models\RequestVolunteer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HelpRequestTest extends TestCase
{
    use RefreshDatabase;

    private function organizationUser(): User
    {
        return User::factory()->organization()->create();
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'type' => 'donation',
            'title' => 'Arrecadação de alimentos',
            'description' => 'Precisamos de cestas básicas para 50 famílias da comunidade.',
            'quantity' => 50,
            'priority' => 'high',
        ], $overrides);
    }

    public function test_organization_creates_request_inheriting_its_location(): void
    {
        $user = $this->organizationUser();
        $user->organization?->update(['latitude' => -23.5, 'longitude' => -46.6]);

        $this->actingAs($user)->postJson('/api/requests', $this->payload())
            ->assertCreated()
            ->assertJsonPath('data.status', 'open')
            ->assertJsonPath('data.latitude', -23.5)
            ->assertJsonPath('data.organization.id', $user->organization?->id);
    }

    public function test_volunteer_cannot_create_requests(): void
    {
        $this->actingAs(User::factory()->volunteer()->create())
            ->postJson('/api/requests', $this->payload())
            ->assertForbidden();
    }

    public function test_request_validation(): void
    {
        $category = Category::factory()->create(['type' => 'service']);

        $this->actingAs($this->organizationUser())
            ->postJson('/api/requests', $this->payload([
                'title' => 'Oi',
                'category_id' => $category->id,
                'start_date' => '2026-10-10',
                'end_date' => '2026-10-01',
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'category_id', 'end_date']);
    }

    public function test_search_filters_by_text_type_and_priority(): void
    {
        HelpRequest::factory()->create(['title' => 'Aulas de matemática', 'type' => 'volunteering', 'priority' => 'urgent']);
        HelpRequest::factory()->create(['title' => 'Doação de roupas', 'type' => 'donation', 'priority' => 'low']);

        $user = User::factory()->volunteer()->create();

        $this->actingAs($user)->getJson('/api/requests?q=matem')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.title', 'Aulas de matemática');

        $this->actingAs($user)->getJson('/api/requests?type=donation')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.type', 'donation');

        $this->actingAs($user)->getJson('/api/requests?priority=urgent')
            ->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_search_filters_and_sorts_by_distance(): void
    {
        $near = HelpRequest::factory()->at(-23.5510, -46.6340)->create();
        $medium = HelpRequest::factory()->at(-23.6000, -46.7000)->create(); // ~8,6 km
        $far = HelpRequest::factory()->at(-22.9068, -43.1729)->create();

        $response = $this->actingAs(User::factory()->volunteer()->create())
            ->getJson('/api/requests?lat=-23.5505&lng=-46.6333&radius=20&sort=distance')
            ->assertOk();

        $this->assertSame([$near->id, $medium->id], collect($response->json('data'))->pluck('id')->all());
        $this->assertNotContains($far->id, collect($response->json('data'))->pluck('id'));
        $this->assertEqualsWithDelta(8.6, $response->json('data.1.distance_km'), 0.5);
    }

    public function test_volunteers_only_see_public_statuses(): void
    {
        HelpRequest::factory()->status(RequestStatus::Open)->create();
        HelpRequest::factory()->status(RequestStatus::Completed)->create();
        HelpRequest::factory()->status(RequestStatus::Cancelled)->create();

        $this->actingAs(User::factory()->volunteer()->create())
            ->getJson('/api/requests?status=completed')
            ->assertOk()
            ->assertJsonCount(2 - 1, 'data')
            ->assertJsonPath('data.0.status', 'open');
    }

    public function test_requests_from_inactive_organizations_are_hidden(): void
    {
        $inactive = Organization::factory()->create(['status' => 'inactive']);
        HelpRequest::factory()->for($inactive)->create();

        $this->actingAs(User::factory()->volunteer()->create())
            ->getJson('/api/requests')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_organization_lists_all_of_its_own_requests_with_mine(): void
    {
        $user = $this->organizationUser();
        HelpRequest::factory()->for($user->organization)->status(RequestStatus::Completed)->create();
        HelpRequest::factory()->for($user->organization)->create();
        HelpRequest::factory()->create();

        $this->actingAs($user)->getJson('/api/requests?mine=1')->assertOk()->assertJsonCount(2, 'data');
        $this->actingAs($user)->getJson('/api/requests?mine=1&status=completed')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_owner_can_update_and_other_organizations_cannot(): void
    {
        $user = $this->organizationUser();
        $request = HelpRequest::factory()->for($user->organization)->create();

        $this->actingAs($user)->putJson("/api/requests/{$request->id}", ['title' => 'Novo título da solicitação'])
            ->assertOk()->assertJsonPath('data.title', 'Novo título da solicitação');

        $this->actingAs($this->organizationUser())->putJson("/api/requests/{$request->id}", ['title' => 'Hackeado'])
            ->assertForbidden();
    }

    public function test_completed_requests_cannot_be_edited(): void
    {
        $user = $this->organizationUser();
        $request = HelpRequest::factory()->for($user->organization)->status(RequestStatus::Completed)->create();

        $this->actingAs($user)->putJson("/api/requests/{$request->id}", ['title' => 'Tentativa de edição'])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Solicitações concluídas ou canceladas não podem ser alteradas.');
    }

    public function test_completing_a_request_completes_approved_and_rejects_pending_volunteers(): void
    {
        $user = $this->organizationUser();
        $request = HelpRequest::factory()->for($user->organization)->status(RequestStatus::InProgress)->create();
        $approved = RequestVolunteer::factory()->status(RequestVolunteerStatus::Approved)->create(['request_id' => $request->id]);
        $pending = RequestVolunteer::factory()->create(['request_id' => $request->id]);

        $this->actingAs($user)->putJson("/api/requests/{$request->id}", ['status' => 'completed'])
            ->assertOk()->assertJsonPath('data.status', 'completed');

        $this->assertSame(RequestVolunteerStatus::Completed, $approved->refresh()->status);
        $this->assertSame(RequestVolunteerStatus::Rejected, $pending->refresh()->status);
    }

    public function test_delete_removes_request_without_volunteers(): void
    {
        $user = $this->organizationUser();
        $request = HelpRequest::factory()->for($user->organization)->create();

        $this->actingAs($user)->deleteJson("/api/requests/{$request->id}")
            ->assertOk()->assertJsonPath('deleted', true);

        $this->assertModelMissing($request);
    }

    public function test_delete_cancels_request_with_volunteers(): void
    {
        $user = $this->organizationUser();
        $request = HelpRequest::factory()->for($user->organization)->create();
        $participation = RequestVolunteer::factory()->create(['request_id' => $request->id]);

        $this->actingAs($user)->deleteJson("/api/requests/{$request->id}")
            ->assertOk()->assertJsonPath('deleted', false)->assertJsonPath('data.status', 'cancelled');

        $this->assertSame(RequestVolunteerStatus::Cancelled, $participation->refresh()->status);
    }

    public function test_categories_are_listed(): void
    {
        Category::factory()->count(3)->create();
        Category::factory()->create(['active' => false]);

        $this->actingAs(User::factory()->volunteer()->create())
            ->getJson('/api/categories')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }
}
