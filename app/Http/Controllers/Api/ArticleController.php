<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ArticleController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', [Article::class, $request->input('status')]);

        $request->validate([
            'status' => ['nullable', 'in:draft,archived,published'],
            'order' => ['nullable', 'in:title,published_at,created_at'],
            'dir' => ['nullable', 'in:asc,desc']
        ]);

        $articles = Article::query()
            ->where('author_uuid', Auth::user()->uuid);

        if ($request->filled('status')) {
            $articles->where('status', $request->input('status'));
        }

        if ($request->filled('dir') && $request->filled('order')) {
            $articles->orderBy($request->input('order'), $request->input('dir'));
        }

        $articles = $articles->paginate($request->input('per_page', 25), ['*'], 'page', $request->input('page', 1));

        return $this->success(['data' => $articles->toArray()]);
    }

    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', Article::class);
        $data = $request->validate([
            'title' => ['required', 'string', 'min:3'],
            'slug' => ['required', 'string', 'min:3', 'unique:articles,slug'],
            'body' => ['required', 'min:50'],
            'tags' => ['required'],
            'status' => ['required', 'in:draft,archived,published'],
            'published_at' => ['required', 'integer', 'min:946684800'],
        ]);
        $data['author_uuid'] = $request->user()->uuid;
        $article = Article::query()->create($data);

        return $this->success(['data' => $article->toArray(), 'code' => 201]);
    }

    public function update(Request $request, $uuid): JsonResponse
    {

        $data = $request->validate([
            'title' => ['nullable', 'string', 'min:3'],
            'slug' => ['nullable', 'string', 'min:3', Rule::unique('articles', 'slug')->ignore($uuid, 'uuid')],
            'body' => ['nullable', 'min:50'],
            'tags' => ['nullable'],
            'status' => ['nullable', 'in:draft,archived,published'],
            'published_at' => ['nullable', 'integer', 'min:946684800'],
        ]);

        $article = Article::query()->where('uuid', $uuid)->first();

        if (is_null($article)) {
            return $this->error([
                'message' => __('Not Found'),
                'code' => 404
            ]);
        }

        Gate::authorize('update', $article);

        $article->update($data);
        $article->refresh();

        return $this->success(['data' => $article->toArray()]);

    }

    public function show(Request $request, $uuid): JsonResponse
    {
        $article = Article::query()->where('uuid', $uuid)->first();
        if (is_null($article)) {
            return $this->error([
                'message' => __('Not Found'),
                'code' => 404
            ]);
        }
        Gate::authorize('view', $article);
        return $this->success(['data' => $article->toArray()]);
    }

    public function destroy(Request $request, $uuid): JsonResponse
    {
        $article = Article::query()->where('uuid', $uuid)->first();
//        if ($article->author_uuid != $request->user()->uuid) {
//            return $this->error([
//                'message' => __('You are not authorized to access this data.'),
//                'code' => 403
//            ]);
//        }
        if (is_null($article)) {
            return $this->error([
                'message' => __('Not Found'),
                'code' => 404
            ]);
        }
        Gate::authorize('delete', $article);

        $res = $article->delete();

        return $res ? $this->success() : $this->error();
    }


}

