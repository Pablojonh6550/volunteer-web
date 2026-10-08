<?php

namespace Tests\Feature\Api;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** Cabeçalho que faz o Sanctum tratar a requisição como vinda do SPA (stateful). */
    private const SPA = ['Referer' => 'http://localhost', 'Accept' => 'application/json'];

    public function test_volunteer_can_register_and_gets_a_profile(): void
    {
        $response = $this->withHeaders(self::SPA)->postJson('/api/auth/register', [
            'name' => 'Ana Souza',
            'email' => 'ana@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'type' => 'volunteer',
            'terms' => true,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.type', 'volunteer')
            ->assertJsonPath('data.volunteer.available', true)
            ->assertJsonMissingPath('token');

        $user = User::where('email', 'ana@example.com')->firstOrFail();
        $this->assertSame(UserType::Volunteer, $user->type);
        $this->assertNotNull($user->volunteer);
        $this->assertAuthenticatedAs($user);
    }

    public function test_organization_can_register_with_institution_name(): void
    {
        $this->withHeaders(self::SPA)->postJson('/api/auth/register', [
            'name' => 'Carlos Lima',
            'organization_name' => 'Instituto Esperança',
            'email' => 'contato@esperanca.org',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'type' => 'organization',
            'terms' => true,
        ])->assertCreated()->assertJsonPath('data.organization.name', 'Instituto Esperança');
    }

    public function test_registration_requires_terms_and_valid_type(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => 'X',
            'email' => 'x@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'type' => 'admin',
        ])->assertUnprocessable()->assertJsonValidationErrors(['type', 'terms']);
    }

    public function test_user_can_login_with_session_from_spa(): void
    {
        $user = User::factory()->volunteer()->create();

        $this->withHeaders(self::SPA)->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk()->assertJsonPath('data.id', $user->id)->assertJsonMissingPath('token');

        $this->assertAuthenticatedAs($user);
    }

    public function test_external_client_receives_bearer_token(): void
    {
        $user = User::factory()->volunteer()->create();

        $token = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk()->json('token');

        $this->assertNotEmpty($token);

        $this->withToken($token)->getJson('/api/me')->assertOk()->assertJsonPath('data.email', $user->email);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'wrong'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->assertGuest();
    }

    public function test_login_is_locked_after_too_many_attempts(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 5) as $ignored) {
            $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'wrong']);
        }

        $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'password'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->volunteer()->create();

        $this->actingAs($user)->withHeaders(self::SPA)->postJson('/api/auth/logout')->assertNoContent();

        $this->assertGuest('web');
    }

    public function test_password_is_hashed_and_never_exposed(): void
    {
        $user = User::factory()->volunteer()->create();

        $this->actingAs($user)->getJson('/api/me')
            ->assertOk()
            ->assertJsonMissingPath('data.password')
            ->assertJsonMissingPath('data.remember_token');

        $this->assertNotSame('password', $user->getAuthPassword());
    }
}
