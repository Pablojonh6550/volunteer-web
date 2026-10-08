<?php

namespace Tests\Feature\Api;

use App\Enums\RequestStatus;
use App\Enums\RequestVolunteerStatus;
use App\Models\HelpRequest;
use App\Models\RequestVolunteer;
use App\Models\User;
use App\Notifications\ParticipationStatusChanged;
use App\Notifications\VolunteerInterestReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Fluxo completo: voluntário demonstra interesse → ONG analisa →
 * aprova/recusa → atividade é realizada → participação concluída.
 */
class InterestFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_interest_flow(): void
    {
        Notification::fake();

        $organizationUser = User::factory()->organization()->create();
        $volunteerUser = User::factory()->volunteer()->create();
        $request = HelpRequest::factory()->for($organizationUser->organization)->create();

        // 1. Voluntário demonstra interesse.
        $participationId = $this->actingAs($volunteerUser)
            ->postJson("/api/requests/{$request->id}/interest", ['message' => 'Posso ajudar aos sábados.'])
            ->assertCreated()
            ->assertJsonPath('data.status', 'interested')
            ->json('data.id');

        Notification::assertSentTo($organizationUser, VolunteerInterestReceived::class);

        // 2. ONG consulta interessados (sem contato antes da aprovação).
        $this->actingAs($organizationUser)->getJson("/api/requests/{$request->id}/volunteers")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.message', 'Posso ajudar aos sábados.')
            ->assertJsonMissingPath('data.0.contact');

        // 3. ONG aprova → contato liberado e solicitação em andamento.
        $this->actingAs($organizationUser)->patchJson("/api/request-volunteers/{$participationId}", ['status' => 'approved'])
            ->assertOk()
            ->assertJsonPath('data.status', 'approved')
            ->assertJsonPath('data.contact.email', $volunteerUser->email);

        $this->assertSame(RequestStatus::InProgress, $request->refresh()->status);
        Notification::assertSentTo($volunteerUser, ParticipationStatusChanged::class);

        // 4. Atividade realizada → participação concluída.
        $this->actingAs($organizationUser)->patchJson("/api/request-volunteers/{$participationId}", ['status' => 'completed'])
            ->assertOk()->assertJsonPath('data.status', 'completed');

        // 5. Aparece no histórico do voluntário.
        $this->actingAs($volunteerUser)->getJson('/api/me/activities')
            ->assertOk()
            ->assertJsonPath('data.0.status', 'completed')
            ->assertJsonPath('data.0.request.id', $request->id);
    }

    public function test_volunteer_cannot_express_interest_twice(): void
    {
        $volunteerUser = User::factory()->volunteer()->create();
        $request = HelpRequest::factory()->create();

        $this->actingAs($volunteerUser)->postJson("/api/requests/{$request->id}/interest")->assertCreated();
        $this->actingAs($volunteerUser)->postJson("/api/requests/{$request->id}/interest")
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Você já demonstrou interesse nesta solicitação.');
    }

    public function test_volunteer_can_cancel_and_express_interest_again(): void
    {
        $volunteerUser = User::factory()->volunteer()->create();
        $request = HelpRequest::factory()->create();

        $this->actingAs($volunteerUser)->postJson("/api/requests/{$request->id}/interest")->assertCreated();
        $this->actingAs($volunteerUser)->deleteJson("/api/requests/{$request->id}/interest")
            ->assertOk()->assertJsonPath('data.status', 'cancelled');
        $this->actingAs($volunteerUser)->postJson("/api/requests/{$request->id}/interest")
            ->assertCreated()->assertJsonPath('data.status', 'interested');

        $this->assertSame(1, RequestVolunteer::count());
    }

    public function test_cannot_express_interest_in_closed_requests(): void
    {
        $request = HelpRequest::factory()->status(RequestStatus::Completed)->create();
        $volunteerUser = User::factory()->volunteer()->create();

        $this->actingAs($volunteerUser)->postJson("/api/requests/{$request->id}/interest")->assertForbidden();
    }

    public function test_organizations_cannot_express_interest(): void
    {
        $request = HelpRequest::factory()->create();

        $this->actingAs(User::factory()->organization()->create())
            ->postJson("/api/requests/{$request->id}/interest")
            ->assertForbidden();
    }

    public function test_only_owner_organization_reviews_volunteers(): void
    {
        $participation = RequestVolunteer::factory()->create();
        $otherOrganization = User::factory()->organization()->create();

        $this->actingAs($otherOrganization)->getJson("/api/requests/{$participation->request_id}/volunteers")->assertForbidden();
        $this->actingAs($otherOrganization)->patchJson("/api/request-volunteers/{$participation->id}", ['status' => 'approved'])->assertForbidden();
    }

    public function test_invalid_participation_transition_is_rejected(): void
    {
        $organizationUser = User::factory()->organization()->create();
        $request = HelpRequest::factory()->for($organizationUser->organization)->create();
        $participation = RequestVolunteer::factory()->status(RequestVolunteerStatus::Rejected)->create(['request_id' => $request->id]);

        $this->actingAs($organizationUser)->patchJson("/api/request-volunteers/{$participation->id}", ['status' => 'approved'])
            ->assertUnprocessable();
    }

    public function test_activities_are_only_for_volunteers(): void
    {
        $this->actingAs(User::factory()->organization()->create())->getJson('/api/me/activities')->assertForbidden();
    }

    public function test_dashboard_returns_stats_by_profile(): void
    {
        $organizationUser = User::factory()->organization()->create();
        $request = HelpRequest::factory()->for($organizationUser->organization)->create();
        RequestVolunteer::factory()->create(['request_id' => $request->id]);

        $this->actingAs($organizationUser)->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('data.requests_by_status.open', 1)
            ->assertJsonPath('data.pending_interests', 1);

        $this->actingAs(User::factory()->volunteer()->create())->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('data.open_requests', 1)
            ->assertJsonStructure(['data' => ['participations_by_status', 'nearby_requests']]);
    }
}
