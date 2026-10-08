<?php

namespace App\Http\Requests\Participations;

use Illuminate\Foundation\Http\FormRequest;

class ExpressInterestRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'message' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
