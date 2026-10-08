<?php

namespace App\Http\Requests\HelpRequests;

use App\Enums\RequestPriority;
use App\Enums\RequestStatus;
use App\Enums\RequestType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Filtros da pesquisa de solicitações (ver App\Services\HelpRequestSearch).
 */
class SearchHelpRequestsRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', Rule::enum(RequestType::class)],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'priority' => ['nullable', Rule::enum(RequestPriority::class)],
            'status' => ['nullable', Rule::enum(RequestStatus::class)],
            'organization_id' => ['nullable', 'integer'],
            'mine' => ['nullable', 'boolean'],
            'lat' => ['nullable', 'numeric', 'between:-90,90', 'required_with:lng,radius'],
            'lng' => ['nullable', 'numeric', 'between:-180,180', 'required_with:lat,radius'],
            'radius' => ['nullable', 'numeric', 'min:1', 'max:200'],
            'sort' => ['nullable', Rule::in(['recent', 'distance', 'priority'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
