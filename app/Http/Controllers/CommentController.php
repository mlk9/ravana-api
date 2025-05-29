<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Comment;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{

    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $comments = Comment::query();
        $comments->where('user_uuid', $request->user()->uuid);
        $comments = $comments
            ->paginate($request->input('per_page', 25), ['*'], 'page', $request->input('page', 1));
        return $this->success(['data' => $comments->toArray()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'text' => ['required', 'string', 'min:3'],
            'rate' => ['required', 'numeric', 'min:0', 'max:5'],
            'type' => ['required', 'string', 'in:article'],
            'id' => ['required', 'string'],
            'parent' => ['nullable', 'string', 'exists:comments,uuid']
        ]);

        $object = null;

        if ($request->input('type') == 'article') {
            $object = Article::query()->where('uuid', $request->input('id'))->firstOrFail();
        }

        $comment = new Comment([
            'text' => $request->input('text'),
            'rate' => $request->input('rate', 0),
            'user_uuid' => $request->user()->uuid,
            'parent_uuid' => $request->input('parent'),
        ]);

        $comment->commentable()->associate($object);

        if ($comment->save() === false) {
            $this->error();
        }

        return $this->success(['data' => $comment->toArray(), 'code' => 201]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $uuid): JsonResponse
    {
        $comment = Comment::query()
                    ->where('uuid',$uuid)
                    ->where('user_uuid',$request->user()->uuid)
                    ->firstOrFail();

        return $this->success(['data' => $comment->toArray()]);
    }

}
