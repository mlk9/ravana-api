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
     * Get a paginated list of published articles
     *
     * @group Articles
     *
     * Retrieve a list of published articles with optional search and sorting.
     * Articles are filtered to only include those with `published` status and a `published_at` date not in the future.
     *
     * @queryParam search string Optional. A search term to filter articles by title. Minimum 3 characters. Example: Laravel
     * @queryParam order string Optional. Field to sort by. Allowed values: title, published_at, created_at. Example: published_at
     * @queryParam dir string Optional. Sort direction. Allowed values: asc, desc. Example: desc
     * @queryParam per_page integer Optional. Number of items per page. Default is 25. Example: 10
     * @queryParam page integer Optional. The page number to return. Default is 1. Example: 2
     *
     * @response 200 scenario="success" {
     *   "data": {
     *     "data": [
     *       {
     *         "uuid": "abc123",
     *         "title": "Article Title",
     *         "body": "Article content...",
     *         "published_at": "2025-06-01T12:00:00Z",
     *         "author": {
     *           "id": 1,
     *           "name": "John Doe"
     *         },
     *         "categories": [
     *           {
     *             "id": 2,
     *             "name": "Tech"
     *           }
     *         ]
     *       }
     *     ],
     *     "links": {
     *       "first": "...",
     *       "last": "...",
     *       "prev": null,
     *       "next": "..."
     *     },
     *     "meta": {
     *       "current_page": 1,
     *       "from": 1,
     *       "last_page": 3,
     *       "per_page": 10,
     *       "to": 10,
     *       "total": 30
     *     }
     *   }
     * }
     */

    public function index(Request $request) : JsonResponse
    {
        $request->validate([
            'search' => ['nullable', 'string', 'min:3'],
            'order' => ['nullable', 'in:title,published_at,created_at'],
            'dir' => ['nullable', 'in:asc,desc']
        ]);

        $articles = Article::query()->with(['author', 'categories'])
            ->where('status', 'published')
            ->where('published_at', '<=', now());

        if ($request->filled('search')) {
            $articles->whereLike('title', '%'.$request->input('search').'%');
        }

        if ($request->filled('dir') && $request->filled('order')) {
            $articles->orderBy($request->input('order'), $request->input('dir'));
        }

        $articles = $articles->paginate($request->input('per_page', 25), ['*'], 'page', $request->input('page', 1));

        return $this->success(['data' => new ArticleCollection($articles)]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $slug) : JsonResponse
    {
        $article = Article::query()
            ->with(['author', 'categories'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->where('published_at', '<=', now())
            ->firstOrFail();
        return $this->success(['data' => new ArticleResource($article)]);
    }

}
