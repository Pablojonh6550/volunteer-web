<?php

namespace Database\Factories;

use App\Enums\OrganizationStatus;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Organization>
 */
class OrganizationFactory extends Factory
{
    /** @var list<string> */
    private const PREFIXES = ['Instituto', 'Associação', 'Fundação', 'Projeto', 'Casa', 'Centro Comunitário', 'Coletivo', 'ONG'];

    /** @var list<string> */
    public const NEIGHBORHOODS = [
        'Sé', 'República', 'Bela Vista', 'Liberdade', 'Consolação', 'Pinheiros', 'Vila Mariana', 'Mooca',
        'Brás', 'Santa Cecília', 'Barra Funda', 'Butantã', 'Lapa', 'Tatuapé', 'Ipiranga', 'Santana',
        'Penha', 'Itaquera', 'Campo Limpo', 'Capão Redondo', 'Jabaquara', 'Saúde', 'Perdizes', 'Cambuci',
    ];

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['type' => 'organization']),
            'name' => self::fakeName(),
            'document' => fake()->unique()->numerify('##############'),
            'description' => fake()->paragraph(),
            'phone' => fake()->numerify('11########'),
            'email' => fake()->companyEmail(),
            'website' => 'https://'.fake()->domainName(),
            'address' => fake()->streetName(),
            'number' => (string) fake()->buildingNumber(),
            'neighborhood' => fake()->randomElement(self::NEIGHBORHOODS),
            'city' => 'São Paulo',
            'state' => 'SP',
            'zip_code' => fake()->numerify('0#######'),
            // Pontos aleatórios na região central de São Paulo.
            'latitude' => fake()->latitude(-23.65, -23.45),
            'longitude' => fake()->longitude(-46.75, -46.55),
            'status' => OrganizationStatus::Active,
        ];
    }

    /** Nome plausível de ONG, também usado como nome do usuário dono. */
    public static function fakeName(): string
    {
        return fake()->randomElement(self::PREFIXES).' '.fake()->lastName();
    }

    /** Posiciona a ONG em coordenadas específicas. */
    public function at(float $latitude, float $longitude): static
    {
        return $this->state(['latitude' => $latitude, 'longitude' => $longitude]);
    }

    /** ONG desativada: não aparece para voluntários. */
    public function inactive(): static
    {
        return $this->state(['status' => OrganizationStatus::Inactive]);
    }
}
