<?php

namespace App\Models;

use App\Enums\RequestVolunteerStatus;
use Database\Factories\RequestVolunteerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Participação de um voluntário em uma solicitação (tabela `request_volunteers`).
 *
 * @property int $id
 * @property int $request_id
 * @property int $volunteer_id
 * @property RequestVolunteerStatus $status
 * @property string|null $message
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read HelpRequest $request
 * @property-read Volunteer $volunteer
 */
#[Fillable(['request_id', 'volunteer_id', 'status', 'message'])]
class RequestVolunteer extends Model
{
    /** @use HasFactory<RequestVolunteerFactory> */
    use HasFactory;

    protected $table = 'request_volunteers';

    protected $attributes = [
        'status' => 'interested',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => RequestVolunteerStatus::class,
        ];
    }

    /** @return BelongsTo<HelpRequest, $this> */
    public function request(): BelongsTo
    {
        return $this->belongsTo(HelpRequest::class, 'request_id');
    }

    /** @return BelongsTo<Volunteer, $this> */
    public function volunteer(): BelongsTo
    {
        return $this->belongsTo(Volunteer::class);
    }
}
