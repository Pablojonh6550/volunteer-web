<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Upload seguro de imagem: valida o conteúdo real do arquivo (MIME detectado,
 * não a extensão enviada), limita tamanho e dimensões e bloqueia SVG
 * (que pode conter scripts).
 */
class UpdateAvatarRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'avatar' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp',
                'mimetypes:image/jpeg,image/png,image/webp',
                'max:2048',
                Rule::dimensions()->maxWidth(4000)->maxHeight(4000),
            ],
        ];
    }
}
