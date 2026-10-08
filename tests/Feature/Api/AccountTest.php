<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_account_data(): void
    {
        $user = User::factory()->volunteer()->create();

        $this->actingAs($user)->putJson('/api/me', [
            'name' => 'Nome Novo',
            'email' => 'novo@example.com',
            'phone' => '11999999999',
        ])->assertOk()->assertJsonPath('data.name', 'Nome Novo');

        $this->assertSame('novo@example.com', $user->refresh()->email);
    }

    public function test_user_can_change_password_with_current_password(): void
    {
        $user = User::factory()->volunteer()->create();

        $this->actingAs($user)->putJson('/api/me/password', [
            'current_password' => 'wrong',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertUnprocessable()->assertJsonValidationErrors('current_password');

        $this->actingAs($user)->putJson('/api/me/password', [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertNoContent();

        $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
    }

    public function test_user_can_upload_a_valid_avatar(): void
    {
        Storage::fake('public');
        $user = User::factory()->volunteer()->create();

        $response = $this->actingAs($user)->postJson('/api/me/avatar', [
            'avatar' => UploadedFile::fake()->image('foto.png', 200, 200),
        ])->assertOk();

        $path = $user->refresh()->avatar;
        $this->assertNotNull($path);
        $this->assertStringNotContainsString('foto', $path, 'O nome original do arquivo deve ser descartado.');
        Storage::disk('public')->assertExists($path);
        $this->assertNotNull($response->json('data.avatar_url'));
    }

    public function test_avatar_rejects_non_image_files(): void
    {
        Storage::fake('public');
        $user = User::factory()->volunteer()->create();

        $this->actingAs($user)->postJson('/api/me/avatar', [
            'avatar' => UploadedFile::fake()->createWithContent('avatar.png', '<?php echo "hack";'),
        ])->assertUnprocessable()->assertJsonValidationErrors('avatar');

        $this->actingAs($user)->postJson('/api/me/avatar', [
            'avatar' => UploadedFile::fake()->create('avatar.svg', 10, 'image/svg+xml'),
        ])->assertUnprocessable()->assertJsonValidationErrors('avatar');
    }

    public function test_user_can_delete_account_and_personal_data(): void
    {
        $user = User::factory()->volunteer()->create();
        $volunteerId = $user->volunteer?->id;

        $this->actingAs($user)->deleteJson('/api/me', ['password' => 'password'])->assertNoContent();

        $this->assertModelMissing($user);
        $this->assertDatabaseMissing('volunteers', ['id' => $volunteerId]);
    }
}
