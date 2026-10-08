<?php

namespace Database\Seeders;

use App\Enums\RequestType;
use App\Models\Category;
use Illuminate\Database\Seeder;

/**
 * Categorias base da plataforma. Idempotente: pode ser executado em produção.
 */
class CategorySeeder extends Seeder
{
    /** @var array<string, list<string>> */
    public const CATEGORIES = [
        'donation' => ['Alimentos', 'Roupas', 'Higiene pessoal', 'Material escolar', 'Móveis', 'Brinquedos'],
        'service' => ['Reparos e manutenção', 'Assessoria jurídica', 'Contabilidade', 'Tecnologia', 'Saúde', 'Design e comunicação'],
        'volunteering' => ['Educação', 'Eventos', 'Cuidado de animais', 'Meio ambiente', 'Apoio a idosos', 'Distribuição de alimentos'],
    ];

    public function run(): void
    {
        foreach (self::CATEGORIES as $type => $names) {
            foreach ($names as $name) {
                Category::firstOrCreate(
                    ['name' => $name, 'type' => RequestType::from($type)],
                    ['active' => true],
                );
            }
        }
    }
}
