<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{

    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) : JsonResponse
    {
        Gate::authorize('viewAny', [Category::class]);

        $request->validate([
            'search' => ['nullable','string', 'min:3'],
//            'status' => ['nullable', 'in:draft,archived,published'],
            'order' => ['nullable', 'in:name,created_at'],
            'dir' => ['nullable', 'in:asc,desc']
        ]);

        $categories = Category::query()
            ->where('creator_uuid', Auth::user()->uuid);

        if ($request->filled('search')) {
            $categories->whereLike('name',  '%'.$request->input('search').'%');
        }

        if ($request->filled('dir') && $request->filled('order')) {
            $categories->orderBy($request->input('order'), $request->input('dir'));
        }

        $categories = $categories
            ->paginate($request->input('per_page', 25), ['*'], 'page', $request->input('page', 1));

        return $this->success(['data' => $categories->toArray()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) : JsonResponse
    {
        Gate::authorize('create', Category::class);
        $data = $request->validate([
            'name' => ['required', 'string', 'min:3'],
            'slug' => ['required', 'string', 'min:3', 'unique:categories,slug'],
            'descriptions' => ['nullable', 'min:50'],
            'parent_uuid' => ['nullable', 'exists:categories,uuid']
        ]);
        $data['creator_uuid'] = $request->user()->uuid;
        $category = Category::query()->create($data);

        return $this->success(['data' => $category->toArray(), 'code' => 201]);
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

        Gate::authorize('view', $category);


        return $this->success(['data' => $category->toArray()]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $uuid) : JsonResponse
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'min:3'],
            'slug' => ['nullable', 'string', 'min:3', Rule::unique('categories','uuid')->ignore($uuid,'uuid')],
            'descriptions' => ['nullable', 'min:50'],
            'parent_uuid' => ['nullable', 'exists:categories,uuid']
        ]);

        $category = Category::query()->where('uuid', $uuid)->first();

        if (is_null($category)) {
            return $this->error([
                'message' => __('Not Found'),
                'code' => 404
            ]);
        }
        Gate::authorize('update', $category);
        $category->update($data);

        $category->refresh();

        return $this->success(['data' => $category->toArray()]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid) : JsonResponse
    {
        $category = Category::query()->where('uuid', $uuid)->first();
        if (is_null($category)) {
            return $this->error([
                'message' => __('Not Found'),
                'code' => 404
            ]);
        }

        Gate::authorize('delete', $category);

        $res = $category->delete();

        return $res ? $this->success() : $this->error();
    }
}
