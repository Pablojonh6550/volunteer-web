<?php

namespace App\Http\Requests\HelpRequests;

use App\Enums\RequestStatus;
use Illuminate\Validation\Rule;

/**
 * Atualização parcial: todos os campos são opcionais e `status` permite
 * mover a solicitação no ciclo de vida (as transições são validadas em
 * App\Actions\Requests\UpdateHelpRequest).
 */
class UpdateHelpRequestRequest extends StoreHelpRequestRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = collect(parent::rules())
            ->map(fn (array $rule) => array_values(array_filter($rule, fn ($r) => $r !== 'required')))
            ->map(fn (array $rule) => ['sometimes', ...$rule])
            ->all();

        $rules['status'] = ['sometimes', Rule::enum(RequestStatus::class)];

        return $rules;
    }
}
