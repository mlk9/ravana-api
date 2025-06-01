<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) : JsonResponse
    {
        $request->validate([
            'search' => ['nullable', 'string', 'min:3'],
            'order' => ['nullable', 'in:name,created_at'],
            'dir' => ['nullable', 'in:asc,desc']
        ]);

        $categories = Category::query();

        if ($request->filled('search')) {
            $categories->whereLike('name', '%'.$request->input('search').'%');
        }

        if ($request->filled('dir') && $request->filled('order')) {
            $categories->orderBy($request->input('order'), $request->input('dir'));
        }

        $categories = $categories
            ->paginate($request->input('per_page', 25), ['*'], 'page', $request->input('page', 1));

        return $this->success(['data' => $categories->toArray()]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $slug) : JsonResponse
    {
        $category = Category::query()->where('slug', $slug)->firstOrFail();
        return $this->success(['data' => $category->toArray()]);
    }


}
