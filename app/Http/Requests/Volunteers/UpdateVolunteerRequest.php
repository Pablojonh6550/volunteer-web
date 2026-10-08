<?php

namespace App\Http\Requests\Volunteers;

use App\Http\Requests\Concerns\AddressRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVolunteerRequest extends FormRequest
{
    use AddressRules;

    protected function prepareForValidation(): void
    {
        $this->normalizeAddress();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'birth_date' => ['nullable', 'date', 'before:-16 years'],
            'phone' => ['nullable', 'string', 'max:20'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'available' => ['sometimes', 'boolean'],
            // Consentimento explícito para uso da localização (LGPD).
            'location_consent' => ['sometimes', 'boolean'],
            'category_ids' => ['sometimes', 'array', 'max:20'],
            'category_ids.*' => ['integer', Rule::exists('categories', 'id')->where('active', true)],
            ...$this->addressRules(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'birth_date.before' => 'É necessário ter pelo menos 16 anos para se voluntariar.',
        ];
    }
}
