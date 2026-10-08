<?php

namespace App\Http\Requests\Organizations;

use Illuminate\Foundation\Http\FormRequest;

class SearchOrganizationsRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'lat' => ['nullable', 'numeric', 'between:-90,90', 'required_with:lng,radius'],
            'lng' => ['nullable', 'numeric', 'between:-180,180', 'required_with:lat,radius'],
            'radius' => ['nullable', 'numeric', 'min:1', 'max:200'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
