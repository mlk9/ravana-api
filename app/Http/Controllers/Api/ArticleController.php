<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ArticleController extends Controller
{
    use ApiResponse;

    public function index(Request $request) : JsonResponse
    {
        $articles = Article::query()
            ->where('author_uuid',Auth::user()->uuid)
            ->paginate(25);

        return $this->success(['data' => $articles->toArray()]);
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

        return $this->success(['data' => $article->toArray(), 'code' => 201]);
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
            return $this->error([
                'message' => __('You are not authorized to access this data.'),
                'code' => 403
            ]);
        }
        if(is_null($article))
        {
            return $this->error([
                'message' => __('Not Found'),
                'code' => 404
            ]);
        }

        $article->update($data);
        $article->refresh();

        return $this->success(['data' => $article->toArray()]);

    }

    public function show(Request $request, $uuid) : JsonResponse
    {
        $article = Article::query()->where('uuid', $uuid)->first();
        if(is_null($article))
        {
            return $this->error([
                'message' => __('Not Found'),
                'code' => 404
            ]);
        }
        return $this->success(['data' => $article->toArray()]);
    }

    public function destroy(Request $request, $uuid) : JsonResponse
    {
        $article = Article::query()->where('uuid', $uuid)->first();
        if($article->author_uuid != $request->user()->uuid)
        {
            return $this->error([
                'message' => __('You are not authorized to access this data.'),
                'code' => 403
            ]);
        }
        if(is_null($article))
        {
            return $this->error([
                'message' => __('Not Found'),
                'code' => 404
            ]);
        }

        $res = $article->delete();

        return $res ? $this->success() : $this->error();
    }


}

