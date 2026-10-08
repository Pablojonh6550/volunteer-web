<?php

namespace App\Models;

use App\Enums\UserType;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

/**
 * Conta de acesso. Todo usuário é uma ONG ou um voluntário (ver UserType),
 * e os dados específicos de cada perfil ficam em Organization ou Volunteer.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property UserType $type
 * @property string|null $phone
 * @property string|null $avatar
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Organization|null $organization
 * @property-read Volunteer|null $volunteer
 */
#[Fillable(['name', 'email', 'password', 'type', 'phone', 'avatar'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $attributes = [
        'phone' => null,
        'avatar' => null,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'type' => UserType::class,
        ];
    }

    /** @return HasOne<Organization, $this> */
    public function organization(): HasOne
    {
        return $this->hasOne(Organization::class);
    }

    /** @return HasOne<Volunteer, $this> */
    public function volunteer(): HasOne
    {
        return $this->hasOne(Volunteer::class);
    }

    public function isOrganization(): bool
    {
        return $this->type === UserType::Organization;
    }

    public function isVolunteer(): bool
    {
        return $this->type === UserType::Volunteer;
    }

    /** URL pública do avatar, quando houver. */
    public function avatarUrl(): ?string
    {
        return $this->avatar ? Storage::disk('public')->url($this->avatar) : null;
    }
}
