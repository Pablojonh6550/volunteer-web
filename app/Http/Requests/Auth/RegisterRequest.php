<?php

namespace App\Http\Requests\Auth;

use App\Enums\UserType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'type' => ['required', Rule::enum(UserType::class)],
            'phone' => ['nullable', 'string', 'max:20'],
            'organization_name' => ['nullable', 'required_if:type,organization', 'string', 'max:255'],
            // LGPD: aceite obrigatório dos termos e da política de privacidade.
            'terms' => ['accepted'],
        ];
    }
}
