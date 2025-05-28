<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
            'body' => ['required', 'min:50'],
            'tags' => ['required'],
        ]);
        $data['author_uuid'] = $request->user()->uuid;
        $article = Article::query()->create($data);

        return response()->json(['article' => $article], 201);
    }

    public function update(Request $request, $uuid) : JsonResponse
    {
        $data = $request->validate([
            'title' => ['nullable'],
            'slug' => ['nullable', Rule::unique('articles','slug')->ignore($uuid,'uuid')],
            'body' => ['nullable', 'min:50'],
            'tags' => ['nullable'],
        ]);

        $article = Article::query()->where('uuid', $uuid)->first();
        if($article->author_uuid != $request->user()->uuid)
        {
            return response()->json('access denied!', 403);
        }
        if(is_null($article))
        {
            return response()->json('not founded!', 404);
        }

        $article->update($data);
        $article->refresh();

        return response()->json(['article' => $article], 200);
    }

    public function show(Request $request, $uuid) : JsonResponse
    {
        $article = Article::query()->where('uuid', $uuid)->first();
        if(is_null($article))
        {
            return response()->json('not founded!', 404);
        }
        return response()->json(['article' => $article], 200);
    }

    public function destroy(Request $request, $uuid) : JsonResponse
    {
        $article = Article::query()->where('uuid', $uuid)->first();
        if($article->author_uuid != $request->user()->uuid)
        {
            return response()->json('access denied!', 403);
        }
        if(is_null($article))
        {
            return response()->json('not founded!', 404);
        }

        $article->delete();

        return response()->json('deleted!', 200);
    }


}

