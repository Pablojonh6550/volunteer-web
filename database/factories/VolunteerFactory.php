<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Volunteer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Volunteer>
 */
class VolunteerFactory extends Factory
{
    /** @var list<string> */
    private const BIOS = [
        'Gosto de ajudar nos fins de semana e tenho experiência com eventos comunitários.',
        'Professora aposentada, disponível para aulas de reforço e leitura.',
        'Estudante universitário buscando contribuir com projetos sociais.',
        'Tenho carro e posso ajudar com transporte de doações.',
        'Trabalho com tecnologia e posso apoiar ONGs com sites e sistemas.',
        'Adoro animais e já atuei como voluntário em abrigos.',
        'Disponível à noite para ajudar na distribuição de alimentos.',
        'Enfermeira, posso apoiar em ações de saúde e prevenção.',
    ];

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'birth_date' => fake()->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
            'phone' => fake()->numerify('119########'),
            'bio' => fake()->randomElement(self::BIOS),
            'address' => fake()->streetName(),
            'number' => (string) fake()->buildingNumber(),
            'neighborhood' => fake()->randomElement(OrganizationFactory::NEIGHBORHOODS),
            'city' => 'São Paulo',
            'state' => 'SP',
            'zip_code' => fake()->numerify('0#######'),
            'latitude' => fake()->latitude(-23.65, -23.45),
            'longitude' => fake()->longitude(-46.75, -46.55),
            'location_consent_at' => now(),
            'available' => true,
        ];
    }

    /** Voluntário que não autorizou o uso da localização. */
    public function withoutLocation(): static
    {
        return $this->state([
            'latitude' => null,
            'longitude' => null,
            'location_consent_at' => null,
        ]);
    }

    /** Voluntário que marcou que não está disponível no momento. */
    public function unavailable(): static
    {
        return $this->state(['available' => false]);
    }
}
