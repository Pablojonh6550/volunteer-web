<?php

namespace App\Models;

use App\Enums\RequestPriority;
use App\Enums\RequestStatus;
use App\Enums\RequestType;
use App\Models\Concerns\HasCoordinates;
use Database\Factories\HelpRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Solicitação publicada por uma ONG (tabela `requests`).
 *
 * O model se chama HelpRequest para não conflitar com Illuminate\Http\Request
 * nos controllers; a API e o banco continuam usando o termo "request".
 *
 * @property int $id
 * @property int $organization_id
 * @property int|null $category_id
 * @property RequestType $type
 * @property string $title
 * @property string $description
 * @property int|null $quantity
 * @property RequestPriority $priority
 * @property RequestStatus $status
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property float|null $latitude
 * @property float|null $longitude
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Organization $organization
 * @property-read Category|null $category
 */
#[Fillable([
    'category_id', 'type', 'title', 'description', 'quantity', 'priority',
    'status', 'start_date', 'end_date', 'latitude', 'longitude',
])]
class HelpRequest extends Model
{
    /** @use HasFactory<HelpRequestFactory> */
    use HasCoordinates, HasFactory;

    protected $table = 'requests';

    protected $attributes = [
        'status' => 'open',
        'priority' => 'medium',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => RequestType::class,
            'priority' => RequestPriority::class,
            'status' => RequestStatus::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'latitude' => 'float',
            'longitude' => 'float',
            'quantity' => 'integer',
        ];
    }

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Vínculos com voluntários (com status do fluxo de interesse).
     *
     * @return HasMany<RequestVolunteer, $this>
     */
    public function participations(): HasMany
    {
        return $this->hasMany(RequestVolunteer::class, 'request_id');
    }

    /** @return BelongsToMany<Volunteer, $this> */
    public function volunteers(): BelongsToMany
    {
        return $this->belongsToMany(Volunteer::class, 'request_volunteers', 'request_id', 'volunteer_id')
            ->withPivot(['id', 'status', 'message'])
            ->withTimestamps();
    }

    public function isOwnedBy(User $user): bool
    {
        return $user->organization !== null && $user->organization->id === $this->organization_id;
    }
}
