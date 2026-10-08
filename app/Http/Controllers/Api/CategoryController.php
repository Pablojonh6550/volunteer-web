<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    /**
     * Lista categorias ativas. Muda raramente, então fica em cache
     * (invalidado automaticamente em Category::booted()).
     */
    public function index(): AnonymousResourceCollection
    {
        $categories = cache()->remember(
            Category::CACHE_KEY,
            now()->addDay(),
            fn () => Category::query()->active()->orderBy('type')->orderBy('name')->get(),
        );

        return CategoryResource::collection($categories);
    }
}
