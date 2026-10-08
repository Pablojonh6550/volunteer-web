<?php

namespace App\Models;

use App\Enums\RequestType;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Categoria de solicitação, agrupada por tipo (doação, serviço, voluntariado).
 *
 * @property int $id
 * @property string $name
 * @property RequestType $type
 * @property bool $active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'type', 'active'])]
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    /** Chave de cache da listagem pública de categorias (ver CategoryController). */
    public const CACHE_KEY = 'categories.active';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => RequestType::class,
            'active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // Invalida o cache sempre que uma categoria muda.
        static::saved(fn () => cache()->forget(self::CACHE_KEY));
        static::deleted(fn () => cache()->forget(self::CACHE_KEY));
    }

    /** @return HasMany<HelpRequest, $this> */
    public function requests(): HasMany
    {
        return $this->hasMany(HelpRequest::class);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('active', true);
    }
}
