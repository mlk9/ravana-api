<?php

namespace App\Http\Controllers;

use App\Http\Resources\ArticleCollection;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ArticleController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) : JsonResponse
    {
        $request->validate([
            'order' => ['nullable', 'in:title,published_at,created_at'],
            'dir' => ['nullable', 'in:asc,desc']
        ]);

        $articles = Article::query()
            ->where('status', 'published')
            ->where('published_at', '<=', now());

        if ($request->filled('dir') && $request->filled('order')) {
            $articles->orderBy($request->input('order'), $request->input('dir'));
        }

        $articles = $articles->paginate($request->input('per_page', 25), ['*'], 'page', $request->input('page', 1));

        return $this->success(['data' => new ArticleCollection($articles)]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid) : JsonResponse
    {
        $article = Article::query()
            ->where('uuid', $uuid)
            ->where('status', 'published')
            ->where('published_at', '<=', now())
            ->first();
        if (is_null($article)) {
            return $this->error([
                'message' => __('Not Found'),
                'code' => 404
            ]);
        }
        return $this->success(['data' => new ArticleResource($article)]);
    }

}
