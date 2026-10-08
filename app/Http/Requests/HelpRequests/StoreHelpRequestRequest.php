<?php

namespace App\Http\Requests\HelpRequests;

use App\Enums\RequestPriority;
use App\Enums\RequestType;
use App\Models\Category;
use App\Models\HelpRequest;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHelpRequestRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(RequestType::class)],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->where('active', true), $this->categoryMatchesType()],
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'priority' => ['sometimes', Rule::enum(RequestPriority::class)],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
        ];
    }

    /**
     * A categoria escolhida precisa ser do mesmo tipo da solicitação.
     */
    protected function categoryMatchesType(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $current = $this->route('helpRequest');
            $type = $this->input('type', $current instanceof HelpRequest ? $current->type->value : null);
            $category = Category::query()->whereKey($value)->first();

            if ($category !== null && $type !== null && $category->type->value !== $type) {
                $fail('A categoria selecionada não pertence ao tipo da solicitação.');
            }
        };
    }
}
