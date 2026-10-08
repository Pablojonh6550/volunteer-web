<?php

namespace App\Models;

use App\Models\Concerns\HasCoordinates;
use Database\Factories\VolunteerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Perfil pessoal do voluntário.
 *
 * @property int $id
 * @property int $user_id
 * @property Carbon|null $birth_date
 * @property string|null $phone
 * @property string|null $bio
 * @property string|null $address
 * @property string|null $number
 * @property string|null $neighborhood
 * @property string|null $city
 * @property string|null $state
 * @property string|null $zip_code
 * @property float|null $latitude
 * @property float|null $longitude
 * @property Carbon|null $location_consent_at
 * @property bool $available
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Collection<int, Category> $categories
 */
#[Fillable([
    'birth_date', 'phone', 'bio', 'address', 'number', 'neighborhood',
    'city', 'state', 'zip_code', 'latitude', 'longitude',
    'location_consent_at', 'available',
])]
class Volunteer extends Model
{
    /** @use HasFactory<VolunteerFactory> */
    use HasCoordinates, HasFactory;

    protected $attributes = [
        'available' => true,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'latitude' => 'float',
            'longitude' => 'float',
            'location_consent_at' => 'datetime',
            'available' => 'boolean',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Áreas de interesse do voluntário.
     *
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * Participações (interesses) em solicitações.
     *
     * @return HasMany<RequestVolunteer, $this>
     */
    public function participations(): HasMany
    {
        return $this->hasMany(RequestVolunteer::class);
    }

    public function hasLocationConsent(): bool
    {
        return $this->location_consent_at !== null;
    }
}
