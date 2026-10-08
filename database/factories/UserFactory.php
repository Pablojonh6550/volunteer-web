<?php

namespace Database\Factories;

use App\Enums\UserType;
use App\Models\Organization;
use App\Models\User;
use App\Models\Volunteer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'type' => UserType::Volunteer,
            'phone' => fake()->numerify('119########'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Usuário do tipo ONG, já com o perfil institucional criado.
     */
    public function organization(): static
    {
        return $this->state(fn () => ['type' => UserType::Organization, 'name' => OrganizationFactory::fakeName()])
            ->afterCreating(function (User $user) {
                Organization::factory()->for($user)->create(['name' => $user->name]);
            });
    }

    /**
     * Usuário do tipo voluntário, já com o perfil pessoal criado.
     */
    public function volunteer(): static
    {
        return $this->state(['type' => UserType::Volunteer])
            ->afterCreating(function (User $user) {
                Volunteer::factory()->for($user)->create();
            });
    }
}
