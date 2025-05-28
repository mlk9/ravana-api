<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArticleController extends Controller
{
    public function index(Request $request) : JsonResponse
    {
        $articles = Article::query()
            ->where('author_uuid',Auth::user()->uuid)
            ->paginate(25);

        return response()->json(['data' => $articles->toArray()]);
    }

    public function store(Request $request) : JsonResponse
    {
        $data = $request->validate([
            'title' => ['required'],
            'slug' => ['required', 'unique:articles,slug'],
            'body' => ['required'],
            'tags' => ['required', 'regex:/^([a-zA-Z0-9_-]+)(,[a-zA-Z0-9_-]+)*$/'],
        ]);
        $data['author_uuid'] = $request->user()->uuid;
        $article = Article::query()->create($data);

        return response()->json(['article' => $article], 201);
    }
}

