<?php

namespace App\Http\Requests\Organizations;

use App\Http\Requests\Concerns\AddressRules;
use App\Models\Organization;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrganizationRequest extends FormRequest
{
    use AddressRules;

    protected function prepareForValidation(): void
    {
        $this->normalizeAddress();

        if ($this->filled('document')) {
            $this->merge(['document' => preg_replace('/\D/', '', (string) $this->input('document'))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Organization $organization */
        $organization = $this->route('organization');

        return [
            'name' => ['required', 'string', 'max:255'],
            'document' => ['nullable', 'digits:14', Rule::unique('organizations')->ignore($organization->id)],
            'description' => ['nullable', 'string', 'max:5000'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url:http,https', 'max:255'],
            ...$this->addressRules(),
        ];
    }
}
