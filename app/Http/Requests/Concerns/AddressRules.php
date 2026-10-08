<?php

namespace App\Http\Requests\Concerns;

/**
 * Regras de endereço/localização compartilhadas entre ONG e voluntário.
 */
trait AddressRules
{
    /**
     * @return array<string, list<string>>
     */
    protected function addressRules(): array
    {
        return [
            'address' => ['nullable', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:20'],
            'neighborhood' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'size:2', 'alpha'],
            'zip_code' => ['nullable', 'digits:8'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
        ];
    }

    /**
     * Normaliza CEP (somente dígitos) e UF (maiúscula) antes de validar.
     */
    protected function normalizeAddress(): void
    {
        $normalized = [];

        if ($this->filled('zip_code')) {
            $normalized['zip_code'] = preg_replace('/\D/', '', (string) $this->input('zip_code'));
        }

        if ($this->filled('state')) {
            $normalized['state'] = mb_strtoupper((string) $this->input('state'));
        }

        $this->merge($normalized);
    }
}
