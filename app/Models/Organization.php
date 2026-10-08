<?php

namespace App\Models;

use App\Enums\OrganizationStatus;
use App\Models\Concerns\HasCoordinates;
use Database\Factories\OrganizationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Organização não governamental. Publica solicitações e avalia voluntários.
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string|null $document
 * @property string|null $description
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $website
 * @property string|null $address
 * @property string|null $number
 * @property string|null $neighborhood
 * @property string|null $city
 * @property string|null $state
 * @property string|null $zip_code
 * @property float|null $latitude
 * @property float|null $longitude
 * @property OrganizationStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 */
#[Fillable([
    'name', 'document', 'description', 'phone', 'email', 'website',
    'address', 'number', 'neighborhood', 'city', 'state', 'zip_code',
    'latitude', 'longitude', 'status',
])]
class Organization extends Model
{
    /** @use HasFactory<OrganizationFactory> */
    use HasCoordinates, HasFactory;

    protected $attributes = [
        'status' => 'active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'status' => OrganizationStatus::class,
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
        $query->where('status', OrganizationStatus::Active);
    }
}
