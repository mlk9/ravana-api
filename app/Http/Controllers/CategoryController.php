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
            'order' => ['nullable', 'in:name,created_at'],
            'dir' => ['nullable', 'in:asc,desc']
        ]);

        $categories = Category::query();

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
    public function show(string $uuid) : JsonResponse
    {
        $category = Category::query()->where('uuid', $uuid)->first();

        if (is_null($category)) {
            return $this->error([
                'message' => __('Not Found'),
                'code' => 404
            ]);
        }

        return $this->success(['data' => $category->toArray()]);
    }


}
