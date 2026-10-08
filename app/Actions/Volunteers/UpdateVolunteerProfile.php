<?php

namespace App\Actions\Volunteers;

use App\Models\Volunteer;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Atualiza o perfil do voluntário, suas áreas de interesse e o consentimento de localização.
 *
 * Regra LGPD: latitude/longitude só são armazenadas quando o voluntário consente
 * (`location_consent = true`). Ao revogar o consentimento, as coordenadas são apagadas.
 */
class UpdateVolunteerProfile
{
    /**
     * @param  array<string, mixed>  $data  Dados validados por UpdateVolunteerRequest.
     */
    public function handle(Volunteer $volunteer, array $data): Volunteer
    {
        return DB::transaction(function () use ($volunteer, $data): Volunteer {
            $attributes = Arr::except($data, ['category_ids', 'location_consent']);

            if (array_key_exists('location_consent', $data)) {
                if ($data['location_consent']) {
                    $attributes['location_consent_at'] = $volunteer->location_consent_at ?? now();
                } else {
                    $attributes['location_consent_at'] = null;
                    $attributes['latitude'] = null;
                    $attributes['longitude'] = null;
                }
            }

            // Sem consentimento vigente, coordenadas enviadas são descartadas.
            $hasConsent = array_key_exists('location_consent_at', $attributes)
                ? $attributes['location_consent_at'] !== null
                : $volunteer->hasLocationConsent();

            if (! $hasConsent) {
                $attributes['latitude'] = null;
                $attributes['longitude'] = null;
            }

            $volunteer->update($attributes);

            if (array_key_exists('category_ids', $data)) {
                $volunteer->categories()->sync($data['category_ids'] ?? []);
            }

            return $volunteer->refresh()->load(['user', 'categories']);
        });
    }
}
