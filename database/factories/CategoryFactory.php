<?php

namespace Database\Factories;

use App\Enums\RequestType;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => ucfirst(fake()->unique()->word()).' '.fake()->word(),
            'type' => fake()->randomElement(RequestType::cases()),
            'active' => true,
        ];
    }
}
