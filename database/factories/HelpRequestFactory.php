<?php

namespace Database\Factories;

use App\Enums\RequestPriority;
use App\Enums\RequestStatus;
use App\Enums\RequestType;
use App\Models\Category;
use App\Models\HelpRequest;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HelpRequest>
 */
class HelpRequestFactory extends Factory
{
    protected $model = HelpRequest::class;

    /**
     * Modelos de título por tipo; ":item" recebe o nome da categoria.
     *
     * @var array<string, list<string>>
     */
    private const TITLES = [
        'donation' => ['Arrecadação de :item', 'Campanha de :item para famílias', 'Precisamos de :item', 'Doação de :item para o abrigo'],
        'service' => ['Apoio profissional em :item', 'Procuramos ajuda com :item', 'Voluntário de :item para a ONG'],
        'volunteering' => ['Mutirão de :item', 'Voluntários para :item', 'Ação de :item no fim de semana'],
    ];

    /** @var array<string, string> */
    private const GENERIC_ITEMS = [
        'donation' => 'itens essenciais',
        'service' => 'manutenção',
        'volunteering' => 'ação comunitária',
    ];

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(RequestType::cases());

        return [
            'organization_id' => Organization::factory(),
            'category_id' => null,
            'type' => $type,
            'title' => self::fakeTitle($type, self::GENERIC_ITEMS[$type->value]),
            'description' => fake()->paragraph(),
            'quantity' => fake()->numberBetween(1, 20),
            'priority' => fake()->randomElement(RequestPriority::cases()),
            'status' => RequestStatus::Open,
            'start_date' => now()->addDays(fake()->numberBetween(1, 10))->toDateString(),
            'end_date' => now()->addDays(fake()->numberBetween(11, 40))->toDateString(),
            'latitude' => fake()->latitude(-23.65, -23.45),
            'longitude' => fake()->longitude(-46.75, -46.55),
        ];
    }

    private static function fakeTitle(RequestType $type, string $item): string
    {
        return str_replace(':item', mb_strtolower($item), fake()->randomElement(self::TITLES[$type->value]));
    }

    public function status(RequestStatus $status): static
    {
        return $this->state(['status' => $status]);
    }

    public function at(float $latitude, float $longitude): static
    {
        return $this->state(['latitude' => $latitude, 'longitude' => $longitude]);
    }

    /** Vincula à categoria, herdando o tipo e gerando um título coerente. */
    public function forCategory(Category $category): static
    {
        return $this->state([
            'category_id' => $category->id,
            'type' => $category->type,
            'title' => self::fakeTitle($category->type, $category->name),
        ]);
    }

    /** Período já encerrado, para solicitações concluídas ou canceladas. */
    public function past(): static
    {
        return $this->state(fn () => [
            'start_date' => now()->subDays(fake()->numberBetween(30, 120))->toDateString(),
            'end_date' => now()->subDays(fake()->numberBetween(1, 29))->toDateString(),
        ]);
    }
}
